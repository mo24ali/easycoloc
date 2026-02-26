<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="EasyColoc – share expenses, split bills, manage your colocation smoothly.">
    <title>EasyColoc / Share, Split wisely</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f5f9;
            color: #1e2b3c;
            overflow-x: hidden;
        }

        .gradient-text {
            background: linear-gradient(145deg, #2563eb, #3b7ab8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-image {
            background-image: url("{{ asset('storage/social_network_hero_1770125540719.png') }}");
            background-size: cover;
            background-position: center;
        }

        /* Fallback if image not found in storage yet */
        .hero-image-fallback {
            background: radial-gradient(circle at center, #2563eb1a 0%, #f2f5f9 70%);
        }

        .btn-vibrant {
            display: block;
            width: 100%;
            padding: 1rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            transition: 0.2s ease;
            text-decoration: none;
            border: 1px solid transparent;
            background: #2563eb;
            color: white;
            box-shadow: 0 5px 12px #2563eb50;
        }
        .btn-vibrant:hover {
            background: #1a4ac4;
            transform: scale(1.01);
        }
    </style>
</head>

<body class="antialiased">
    <div class="flex flex-col lg:flex-row min-h-screen">
        <!-- Image Side (Hidden on Mobile) -->
        <div
            class="hidden lg:flex lg:w-1/2 relative hero-image items-center justify-center p-12 overflow-hidden border-r border-[#dae2ec]">
            <!-- Content overlay for image side -->
            <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>

            <div class="relative z-10 text-center space-y-6">
                <div
                    class="w-24 h-24 bg-white/30 backdrop-blur-xl rounded-3xl flex items-center justify-center mx-auto border border-white/40 shadow-2xl">
                    <span class="text-black text-5xl font-black italic tracking-tighter">E</span>
                </div>
                <h2 class="text-4xl font-extrabold gradient-text tracking-tight leading-tight">
                    Split bills,<br>
                    <span class="text-[#2563eb]">no stress.</span>
                </h2>
            </div>
        </div>

        <!-- content Side -->
        <div class="flex-1 flex flex-col justify-center p-6 sm:p-12 lg:p-20 relative overflow-hidden bg-white">
            <!-- Background mesh (soft) -->
            <div
                class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-[500px] h-[500px] bg-blue-200/30 blur-[120px] rounded-full">
            </div>
            <div
                class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-[500px] h-[500px] bg-slate-200/30 blur-[120px] rounded-full">
            </div>

            <div class="relative z-10 max-w-lg mx-auto lg:mx-0">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-12">
                    <div class="w-12 h-12 bg-[#2563eb] rounded-xl flex items-center justify-center">
                        <span class="text-black text-2xl font-black italic">E</span>
                    </div>
                </div>

                <h1 class="text-5xl sm:text-7xl font-black tracking-tighter mb-8 leading-[0.9] text-[#142c3e]">
                    Share <br>
                    <span class="gradient-text">wisely.</span>
                </h1>

                <p class="text-xl sm:text-2xl font-semibold mb-12 text-[#4b6379] tracking-tight border-l-4 border-[#2563eb] pl-4 bg-[#f4f9ff] py-2 rounded-r-xl">
                    Less pain splitting bills , use EasyColoc.
                </p>

                <div class="space-y-6">
                    <h3 class="text-xl font-extrabold text-[#1b364b]">Join the community.</h3>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-vibrant w-full block text-center py-4 text-lg">
                                Return to Dashboard
                            </a>
                        @else
                            <div class="space-y-4">
                                <a href="{{ route('register') }}" class="btn-vibrant w-full block text-center py-4 text-lg">
                                    Create account
                                </a>
                                <p class="text-[11px] text-[#657e9a] leading-relaxed">
                                    By signing up, you agree to the <a href="#" class="text-[#2563eb] hover:underline">Terms of
                                        Service</a> and <a href="#" class="text-[#2563eb] hover:underline">Privacy Policy</a>,
                                    including <a href="#" class="text-[#2563eb] hover:underline">Cookie Use</a>.
                                </p>
                            </div>

                            <div class="pt-12 space-y-4">
                                <h4 class="font-bold text-lg text-[#1b364b]">Already have an account?</h4>
                                <a href="{{ route('login') }}"
                                    class="w-full block text-center py-4 text-lg border border-[#d3e0f0] rounded-full font-bold text-[#2563eb] bg-white hover:bg-[#f2f9ff] transition-all">
                                    Sign in
                                </a>
                            </div>
                        @endauth
                    @endif
                </div>

                <!-- Footer Links -->
                <footer class="mt-20 flex flex-wrap gap-x-6 gap-y-2 text-xs text-[#6a819b]">
                    <a href="#" class="hover:underline">About</a>
                    <a href="#" class="hover:underline">Help Center</a>
                    <a href="#" class="hover:underline">Terms of Service</a>
                    <a href="#" class="hover:underline">Privacy Policy</a>
                    <a href="#" class="hover:underline">Cookie Policy</a>
                    <a href="#" class="hover:underline">Accessibility</a>
                    <a href="#" class="hover:underline">Ads info</a>
                    <a href="#" class="hover:underline">Blog</a>
                    <a href="#" class="hover:underline">Status</a>
                    <a href="#" class="hover:underline">Careers</a>
                    <a href="#" class="hover:underline">Brand Resources</a>
                    <a href="#" class="hover:underline">Advertising</a>
                    <a href="#" class="hover:underline">Marketing</a>
                    <p class="mt-4 text-[#7891aa]">© 2026 EasyColoc Corp.</p>
                </footer>
            </div>
        </div>
    </div>
</body>

</html>
