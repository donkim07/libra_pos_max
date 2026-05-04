<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LibraPOS | Manufacturing POS for Tanzania')</title>
    <meta name="description" content="@yield('meta_description', 'Multi-tenant POS and manufacturing management software for Tanzanian businesses.')">
    <meta property="og:title" content="@yield('og_title', 'LibraPOS | Manufacturing POS for Tanzania')">
    <meta property="og:description" content="@yield('og_description', 'Manufacture, manage inventory, and sell with confidence using one modern system.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Outfit:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Outfit', sans-serif;
        }

        .bg-grid {
            background-image: linear-gradient(rgba(245, 158, 11, 0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(245, 158, 11, 0.08) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 antialiased">
    <div x-data="{ menuOpen: false }" class="min-h-screen">
        <header class="sticky top-0 z-40 border-b border-amber-400/20 bg-slate-950/90 backdrop-blur">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('landing.home') }}"
                    class="text-xl font-extrabold tracking-tight text-amber-400">LibraPOS</a>
                <div class="hidden items-center gap-7 text-sm font-medium md:flex">
                    <a href="{{ route('landing.home') }}" class="hover:text-amber-300">Home</a>
                    <a href="{{ route('landing.features') }}" class="hover:text-amber-300">Features</a>
                    <a href="{{ route('landing.pricing') }}" class="hover:text-amber-300">Pricing</a>
                    <a href="{{ route('landing.about') }}" class="hover:text-amber-300">About</a>
                    <a href="{{ route('landing.contact') }}" class="hover:text-amber-300">Contact</a>
                </div>
                <div class="hidden items-center gap-3 md:flex">
                    <a href="/admin/login"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold hover:border-amber-300 hover:text-amber-300">Login</a>
                    <a href="/admin/login"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-400">Start
                        Free Trial</a>
                </div>
                <button @click="menuOpen = !menuOpen" class="md:hidden rounded-lg border border-slate-600 p-2"
                    aria-label="Toggle Menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </nav>
            <div x-show="menuOpen" x-transition class="border-t border-amber-400/20 px-4 py-4 md:hidden">
                <div class="flex flex-col gap-3 text-sm font-medium">
                    <a href="{{ route('landing.home') }}">Home</a>
                    <a href="{{ route('landing.features') }}">Features</a>
                    <a href="{{ route('landing.pricing') }}">Pricing</a>
                    <a href="{{ route('landing.about') }}">About</a>
                    <a href="{{ route('landing.contact') }}">Contact</a>
                    <a href="/admin/login"
                        class="mt-2 rounded-lg border border-slate-600 px-4 py-2 text-center">Login</a>
                    <a href="/admin/login"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-center font-semibold text-slate-950">Start Free
                        Trial</a>
                </div>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="border-t border-slate-800 bg-slate-900">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <h3 class="text-lg font-bold text-amber-400">LibraPOS</h3>
                    <p class="mt-3 text-sm text-slate-300">Built for Tanzanian manufacturers to run production, stock,
                        and sales in one place.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Quick Links</h4>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li><a href="{{ route('landing.home') }}" class="hover:text-amber-300">Home</a></li>
                        <li><a href="{{ route('landing.features') }}" class="hover:text-amber-300">Features</a></li>
                        <li><a href="{{ route('landing.pricing') }}" class="hover:text-amber-300">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Product</h4>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li><a href="/admin/login" class="hover:text-amber-300">Login to Dashboard</a></li>
                        <li><a href="{{ route('landing.about') }}" class="hover:text-amber-300">About Us</a></li>
                        <li><a href="{{ route('landing.contact') }}" class="hover:text-amber-300">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Contact</h4>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li>Dar es Salaam, Tanzania</li>
                        <li>+255748224536</li>
                        <li>librapos@rejoda.co.tz</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 px-4 py-4 text-center text-xs text-slate-400">
                © {{ date('Y') }} LibraPOS. Made in Tanzania.
            </div>
        </footer>

        <a href="/admin/login"
            class="fixed bottom-4 left-1/2 z-50 w-[92%] -translate-x-1/2 rounded-xl bg-amber-500 px-4 py-3 text-center text-sm font-bold text-slate-950 shadow-lg md:hidden">
            Start Free Trial
        </a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AOS) AOS.init({
                once: true,
                duration: 700
            });
        });
    </script>
</body>

</html>
