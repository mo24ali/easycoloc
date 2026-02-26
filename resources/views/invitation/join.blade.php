<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join {{ $invitation->collocation->name }} — EasyColoc</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#f2f5f9] min-h-screen flex items-center justify-center px-4 py-16">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center mb-8">
            <div class="w-14 h-14 bg-[#2563eb] rounded-2xl flex items-center justify-center shadow-lg">
                <span class="text-white text-3xl font-black italic tracking-tighter">E</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-[#dae2ec] p-8 text-center">
            <p class="text-xs font-bold text-[#657e9a] uppercase tracking-widest mb-2">You're invited!</p>
            <h1 class="text-2xl font-black text-[#142c3e] tracking-tight mb-1">
                Join <span class="text-[#2563eb]">{{ $invitation->collocation->name }}</span>
            </h1>
            <p class="text-sm text-[#657e9a] mb-6">
                <strong class="text-[#4b6379]">{{ $invitation->sender->name }}</strong> has invited you to join their
                collocation on EasyColoc.
            </p>

            <div
                class="bg-[#f4f9ff] border border-[#dae2ec] rounded-2xl p-4 text-sm text-[#4b6379] mb-8 text-left space-y-1">
                <p>🏠 <strong>Collocation:</strong> {{ $invitation->collocation->name }}</p>
                <p>👤 <strong>Invited by:</strong> {{ $invitation->sender->name }}</p>
                <p>⏰ <strong>Expires:</strong> {{ $invitation->expires_at->format('d M Y') }}</p>
            </div>

            {{-- Register with email pre-filled --}}
            <a href="{{ route('register') }}?invite={{ $invitation->token }}&email={{ urlencode($invitation->email) }}"
                class="block w-full py-4 text-base font-bold bg-[#2563eb] text-white rounded-full shadow-sm hover:bg-[#1a4ac4] transition-all text-center">
                Create account &amp; Join
            </a>
            <p class="mt-4 text-sm text-[#657e9a]">
                Already have an account?
                <a href="{{ route('login') }}?invite={{ $invitation->token }}"
                    class="text-[#2563eb] font-bold hover:underline">Log in to join</a>
            </p>
        </div>
    </div>

</body>

</html>