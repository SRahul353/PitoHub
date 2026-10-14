<?php

namespace App\Services\CPanel;

use App\Models\Server;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CPanelApiService
{
    protected Server $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Factory method
     */
    public static function forServer(Server $server): self
    {
        return new self($server);
    }

    /**
     * Execute a WHM API 1 call
     */
    public function whmCall(string $function, array $params = []): array
    {
        if ($this->server->mock_mode) {
            return $this->handleMockWhmCall($function, $params);
        }

        $url = "{$this->server->base_url}/json-api/{$function}";
        $params['api.version'] = 1;

        try {
            $client = Http::timeout(20)
                ->withoutVerifying(); // Useful for self-signed cPanel certificates

            if ($this->server->auth_type === 'api_token' && $this->server->api_token) {
                $client = $client->withHeaders([
                    'Authorization' => "whm {$this->server->username}:{$this->server->api_token}",
                ]);
            } elseif ($this->server->password) {
                $client = $client->withBasicAuth($this->server->username, $this->server->password);
            }

            $response = $client->get($url, $params);

            if ($response->successful()) {
                return $response->json() ?? ['status' => 1, 'data' => []];
            }

            return [
                'metadata' => [
                    'result' => 0,
                    'reason' => "WHM API Error: HTTP " . $response->status() . " - " . $response->body(),
                ],
            ];
        } catch (\Throwable $e) {
            Log::error("WHM Call to {$this->server->hostname} failed: " . $e->getMessage());

            // If connection fails and mock fallback is acceptable
            return [
                'metadata' => [
                    'result' => 0,
                    'reason' => 'Connection failed: ' . $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Execute a cPanel UAPI call through WHM
     */
    public function uapiCall(string $cpanelUser, string $module, string $function, array $params = []): array
    {
        if ($this->server->mock_mode) {
            return $this->handleMockUapiCall($cpanelUser, $module, $function, $params);
        }

        $url = "{$this->server->base_url}/json-api/cpanel";
        $whmParams = array_merge([
            'api.version' => 1,
            'cpanel_jsonapi_user' => $cpanelUser,
            'cpanel_jsonapi_apiversion' => 3,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $function,
        ], $params);

        try {
            $client = Http::timeout(20)->withoutVerifying();

            if ($this->server->auth_type === 'api_token' && $this->server->api_token) {
                $client = $client->withHeaders([
                    'Authorization' => "whm {$this->server->username}:{$this->server->api_token}",
                ]);
            } elseif ($this->server->password) {
                $client = $client->withBasicAuth($this->server->username, $this->server->password);
            }

            $response = $client->get($url, $whmParams);

            if ($response->successful()) {
                return $response->json() ?? ['status' => 1, 'data' => []];
            }

            return [
                'result' => [
                    'status' => 0,
                    'errors' => ["UAPI Error: HTTP " . $response->status()],
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'result' => [
                    'status' => 0,
                    'errors' => ['UAPI Call failed: ' . $e->getMessage()],
                ],
            ];
        }
    }

    /**
     * Test connection to server
     */
    public function testConnection(): array
    {
        if ($this->server->mock_mode) {
            return [
                'success' => true,
                'version' => '11.118.0.12 (Mock Mode)',
                'hostname' => $this->server->hostname,
                'message' => 'Successfully connected to simulated cPanel/WHM server.',
            ];
        }

        $res = $this->whmCall('version');
        $result = $res['metadata']['result'] ?? ($res['version'] ? 1 : 0);

        if ($result == 1) {
            return [
                'success' => true,
                'version' => $res['version'] ?? ($res['data']['version'] ?? 'Unknown'),
                'hostname' => $this->server->hostname,
                'message' => 'Connection verified successfully.',
            ];
        }

        return [
            'success' => false,
            'error' => $res['metadata']['reason'] ?? 'Could not authenticate with WHM server.',
        ];
    }

    /**
     * List accounts from WHM server
     */
    public function listAccounts(): array
    {
        return $this->whmCall('listaccts');
    }

    /**
     * Create account on WHM
     */
    public function createAccount(array $data): array
    {
        return $this->whmCall('createacct', [
            'username' => $data['username'],
            'domain' => $data['domain'],
            'plan' => $data['plan'] ?? 'default',
            'contactemail' => $data['contact_email'] ?? '',
            'password' => $data['password'] ?? 'PitoHub_' . bin2hex(random_bytes(4)),
            'quota' => $data['disk_limit_mb'] ?? 5120,
            'bwlimit' => $data['bandwidth_limit_mb'] ?? 51200,
        ]);
    }

    /**
     * Suspend cPanel account
     */
    public function suspendAccount(string $username, string $reason = 'Suspended via PitoHub API'): array
    {
        return $this->whmCall('suspendacct', [
            'user' => $username,
            'reason' => $reason,
        ]);
    }

    /**
     * Unsuspend cPanel account
     */
    public function unsuspendAccount(string $username): array
    {
        return $this->whmCall('unsuspendacct', [
            'user' => $username,
        ]);
    }

    /**
     * Terminate / Remove cPanel account
     */
    public function terminateAccount(string $username, bool $keepDns = false): array
    {
        return $this->whmCall('removeacct', [
            'user' => $username,
            'keepdns' => $keepDns ? 1 : 0,
        ]);
    }

    /**
     * Change cPanel package / plan
     */
    public function changePackage(string $username, string $package): array
    {
        return $this->whmCall('changepackage', [
            'user' => $username,
            'pkg' => $package,
        ]);
    }

    /**
     * Change cPanel password
     */
    public function changePassword(string $username, string $password): array
    {
        return $this->whmCall('passwd', [
            'user' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Create SSO / User Session URL to access cPanel without password
     */
    public function createSessionUrl(string $username, string $service = 'cpaneld'): array
    {
        if ($this->server->mock_mode) {
            return [
                'status' => 1,
                'url' => "{$this->server->base_url}/cpsess" . rand(1000000000, 9999999999) . "/login/?user={$username}",
                'token' => 'cpsess_mock_' . bin2hex(random_bytes(8)),
            ];
        }

        $res = $this->whmCall('create_user_session', [
            'user' => $username,
            'service' => $service,
        ]);

        if (isset($res['data']['url'])) {
            return [
                'status' => 1,
                'url' => $res['data']['url'],
                'token' => $res['data']['session_token'] ?? null,
            ];
        }

        return [
            'status' => 0,
            'error' => $res['metadata']['reason'] ?? 'Failed to generate session URL',
        ];
    }

    /**
     * Get Server Load and Disk Usage
     */
    public function getServerStats(): array
    {
        if ($this->server->mock_mode) {
            return [
                'load_avg' => [0.42, 0.58, 0.65],
                'memory_usage' => '3.8 GB / 16.0 GB (23.7%)',
                'disk_usage' => '120.4 GB / 1000.0 GB (12.0%)',
                'cpanel_version' => '11.118.0.12',
                'apache_version' => 'Apache/2.4.58',
                'php_version' => '8.3.6',
                'mysql_version' => '10.6.18-MariaDB',
            ];
        }

        $loadRes = $this->whmCall('systemloadavg');
        return [
            'load_avg' => [
                $loadRes['data']['one'] ?? 0.25,
                $loadRes['data']['five'] ?? 0.30,
                $loadRes['data']['fifteen'] ?? 0.28,
            ],
            'cpanel_version' => 'Active',
        ];
    }

    /**
     * Simulated WHM Call Handler for local mock/testing
     */
    protected function handleMockWhmCall(string $function, array $params): array
    {
        return match ($function) {
            'version' => [
                'metadata' => ['result' => 1, 'reason' => 'OK'],
                'version' => '11.118.0.12',
                'data' => ['version' => '11.118.0.12'],
            ],
            'listaccts' => [
                'metadata' => ['result' => 1, 'reason' => 'OK'],
                'data' => [
                    'acct' => [
                        [
                            'user' => 'sampleuser',
                            'domain' => 'sampledomain.com',
                            'plan' => 'Unlimited',
                            'email' => 'admin@sampledomain.com',
                            'diskused' => '250M',
                            'disklimit' => '5000M',
                            'suspended' => 0,
                        ],
                    ],
                ],
            ],
            'createacct' => [
                'metadata' => ['result' => 1, 'reason' => "Account {$params['username']} created successfully on mock server"],
                'data' => [
                    'ip' => $this->server->ip_address ?? '127.0.0.1',
                    'nameserver' => 'ns1.pitohub.local',
                ],
            ],
            'suspendacct' => [
                'metadata' => ['result' => 1, 'reason' => "Account {$params['user']} suspended successfully"],
            ],
            'unsuspendacct' => [
                'metadata' => ['result' => 1, 'reason' => "Account {$params['user']} unsuspended successfully"],
            ],
            'removeacct' => [
                'metadata' => ['result' => 1, 'reason' => "Account {$params['user']} removed successfully"],
            ],
            'changepackage' => [
                'metadata' => ['result' => 1, 'reason' => "Package changed to {$params['pkg']} for user {$params['user']}"],
            ],
            'passwd' => [
                'metadata' => ['result' => 1, 'reason' => "Password changed successfully for {$params['user']}"],
            ],
            'create_user_session' => [
                'metadata' => ['result' => 1, 'reason' => 'OK'],
                'data' => [
                    'url' => "{$this->server->base_url}/cpsess" . rand(10000000, 99999999) . "/login/?user={$params['user']}",
                    'session_token' => 'mock_token_' . bin2hex(random_bytes(6)),
                ],
            ],
            default => [
                'metadata' => ['result' => 1, 'reason' => 'Mock operation completed'],
            ],
        };
    }

    /**
     * Simulated UAPI Call Handler
     */
    protected function handleMockUapiCall(string $cpanelUser, string $module, string $function, array $params): array
    {
        return [
            'result' => [
                'status' => 1,
                'data' => [
                    'message' => "Simulated UAPI {$module}::{$function} executed for user {$cpanelUser}",
                ],
                'errors' => null,
            ],
        ];
    }
}
