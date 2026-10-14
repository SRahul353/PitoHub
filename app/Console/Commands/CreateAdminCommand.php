<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pitohub:create-admin {--email=admin@pitohub.local} {--name=Admin} {--password=password123}';

    /**
     * The console command description.
     */
    protected $description = 'Create a PitoHub administrator user and display an API token';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        $token = $user->createToken('PitoHub CLI Admin Token')->plainTextToken;

        $this->info("Administrator account ready!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Password', $password],
                ['Sanctum API Token', $token],
            ]
        );

        $this->warn("Keep your API token safe! Use 'Authorization: Bearer <TOKEN>' for API requests.");

        return Command::SUCCESS;
    }
}
