<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PitoHub - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white relative overflow-hidden">

    <!-- Background glowing gradients -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10 space-y-6">

        <!-- Logo & Title -->
        <div class="text-center space-y-2">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-indigo-600 items-center justify-center font-extrabold text-white shadow-xl shadow-indigo-500/20 text-2xl mx-auto">
                P
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Sign in to PitoHub</h1>
            <p class="text-sm text-slate-400">cPanel Hosting Management & REST API Control Panel</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl space-y-6">

            <!-- Flash Error Messages -->
            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@pitohub.local') }}" required autofocus
                        class="w-full bg-slate-800/90 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition outline-none"
                        placeholder="admin@pitohub.local">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" name="password" required
                        class="w-full bg-slate-800/90 border border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition outline-none"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer text-slate-400 select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0">
                        <span>Remember me</span>
                    </label>
                    <span class="text-slate-500 text-[11px]">Default: password123</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 font-semibold text-sm text-white shadow-lg shadow-indigo-600/30 transition duration-150 flex items-center justify-center space-x-2">
                    <span>Sign In to Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800/80 space-y-1.5 text-xs text-slate-400">
                <div class="font-semibold text-slate-300 flex items-center justify-between">
                    <span>Default Admin Account</span>
                    <button type="button" onclick="fillAdmin()" class="text-indigo-400 hover:text-indigo-300 font-medium text-[11px]">Fill Form</button>
                </div>
                <div class="font-mono text-[11px] text-slate-400 flex justify-between">
                    <span>Email: <span class="text-slate-200">admin@pitohub.local</span></span>
                    <span>Pass: <span class="text-slate-200">password123</span></span>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500">
            PitoHub &copy; {{ date('Y') }} &bull; cPanel & WHM Management Suite
        </div>
    </div>

    <script>
        function fillAdmin() {
            document.querySelector('input[name="email"]').value = 'admin@pitohub.local';
            document.querySelector('input[name="password"]').value = 'password123';
        }
    </script>
</body>
</html>
