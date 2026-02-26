<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invalid Invitation — EasyColoc</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-[#f2f5f9] min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-3xl border border-red-100 shadow-sm p-10 text-center">
        <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v4m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z" />
            </svg>
        </div>
        <h1 class="text-xl font-bold text-red-600 mb-2">Invitation {{ $reason ?? 'Invalid' }}</h1>
        <p class="text-sm text-[#657e9a] mb-8">
            @if($reason === 'expired')
                This invitation link has expired. Please ask the collocation owner to send you a new one.
            @elseif($reason === 'accepted')
                This invitation has already been used.
            @else
                This invitation link is no longer valid.
            @endif
        </p>
        <a href="{{ route('login') }}"
            class="inline-block px-6 py-3 bg-[#2563eb] text-white font-bold rounded-full text-sm hover:bg-[#1a4ac4] transition-all">
            Back to Login
        </a>
    </div>
</body>

</html>