@extends('layouts.landing')

@section('title', 'LibraPOS | Manufacture, Manage, Sell')
@section('meta_description', 'Manufacture, track stock, and run POS sales in one modern SaaS platform for Tanzanian businesses.')
@section('meta_keywords', 'POS for manufacturers Tanzania, manufacturing ERP lite Tanzania, inventory and invoicing software, SaaS POS Dar es Salaam')
@section('canonical_url', route('landing.home'))
@section('og_title', 'LibraPOS | Manufacture, Manage, Sell')
@section('og_description', 'A multi-tenant POS built for Tanzanian manufacturing and trading operations.')
@section('og_url', route('landing.home'))

@section('content')
<section class="bg-grid border-b border-slate-800">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24">
        <div data-aos="fade-up">
            <p class="inline-flex items-center rounded-full border border-amber-400/40 bg-amber-400/10 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-amber-300">
                7-Day Free Trial • No Credit Card Required
            </p>
            <h1 class="mt-5 text-4xl font-extrabold leading-tight sm:text-5xl">Manufacture. Manage. Sell.<br>All in One Place.</h1>
            <p class="mt-5 max-w-xl text-lg text-slate-300">LibraPOS helps Tanzanian manufacturers control production, inventory, and sales from one reliable cloud workspace.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ url('/admin') }}" class="rounded-lg bg-amber-500 px-6 py-3 text-sm font-bold text-slate-950 hover:bg-amber-400">Dashboard</a>
                @else
                    <a href="{{ url('/admin/login') }}" class="rounded-lg bg-amber-500 px-6 py-3 text-sm font-bold text-slate-950 hover:bg-amber-400">Start Free Trial</a>
                @endauth
                <a href="{{ route('landing.features') }}" class="rounded-lg border border-slate-600 px-6 py-3 text-sm font-semibold hover:border-amber-300 hover:text-amber-300">See Features</a>
            </div>
        </div>
        <div data-aos="fade-left" class="rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl shadow-slate-950/80">
            <div class="rounded-xl border border-slate-700 bg-slate-950 p-4">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Production Dashboard</span>
                    <span class="rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-300">Live</span>
                </div>
                <div class="space-y-3">
                    <div class="h-3 rounded bg-slate-800"></div>
                    <div class="h-3 w-5/6 rounded bg-slate-800"></div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="h-20 rounded bg-slate-800"></div>
                        <div class="h-20 rounded bg-slate-800"></div>
                        <div class="h-20 rounded bg-slate-800"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="border-b border-slate-800 bg-slate-900">
    <div class="mx-auto max-w-7xl px-4 py-10 text-center sm:px-6 lg:px-8">
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-slate-400">Trusted by manufacturers across Tanzania</p>
        <div class="mt-6 grid gap-4 text-sm text-slate-300 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-700 py-3">Leymax Cakes</div>
            <div class="rounded-lg border border-slate-700 py-3">Rejoda</div>
            <div class="rounded-lg border border-slate-700 py-3">SKS Pharma Ltd.</div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-6 md:grid-cols-3">
        <article class="rounded-xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="text-lg font-bold">Manual stock tracking costs money</h3>
            <p class="mt-2 text-sm text-slate-300">Move from notebooks to real-time inventory by store and avoid silent stock loss.</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="text-lg font-bold">Bulk orders become operational chaos</h3>
            <p class="mt-2 text-sm text-slate-300">Create and manage production batches and bulk manufacturing workflows with clear status visibility.</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="text-lg font-bold">Sales and production stay disconnected</h3>
            <p class="mt-2 text-sm text-slate-300">Link manufacturing, purchasing, stock movement, and POS sales inside one tenant workspace.</p>
        </article>
    </div>
</section>

<section class="border-y border-slate-800 bg-slate-900/70">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-3xl font-bold">Key Features</h2>
            <a href="{{ route('landing.features') }}" class="text-sm font-semibold text-amber-300">Learn more →</a>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="rounded-xl border border-slate-800 p-5">
                <h3 class="font-bold">Manufacturing Batch Tracking</h3>
                <p class="mt-2 text-sm text-slate-300">Track production records, item usage, and outputs without spreadsheets.</p>
            </div>
            <div class="rounded-xl border border-slate-800 p-5">
                <h3 class="font-bold">Multi-warehouse Inventory</h3>
                <p class="mt-2 text-sm text-slate-300">Manage stock quantities by store location with movement and adjustment logs.</p>
            </div>
            <div class="rounded-xl border border-slate-800 p-5">
                <h3 class="font-bold">POS + Bulk Sales Invoicing</h3>
                <p class="mt-2 text-sm text-slate-300">Issue sales and order records fast, with printable transaction workflows.</p>
            </div>
            <div class="rounded-xl border border-slate-800 p-5">
                <h3 class="font-bold">Secure Multi-tenant Isolation</h3>
                <p class="mt-2 text-sm text-slate-300">Every business operates in an isolated workspace with its own data and team.</p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-3xl font-bold">Simple Pricing in TZS</h2>
        <a href="{{ route('landing.pricing') }}" class="text-sm font-semibold text-amber-300">Compare all plans →</a>
    </div>
    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-800 p-6">
            <h3 class="font-bold">Starter</h3>
            <p class="mt-2 text-2xl font-extrabold">TZS 12,000<span class="text-sm font-normal text-slate-400">/month</span></p>
        </div>
        <div class="rounded-xl border border-amber-400 bg-amber-400/10 p-6">
            <h3 class="font-bold text-amber-300">Business</h3>
            <p class="mt-2 text-2xl font-extrabold">TZS 22,000<span class="text-sm font-normal text-slate-400">/month</span></p>
        </div>
        <div class="rounded-xl border border-slate-800 p-6">
            <h3 class="font-bold">Enterprise</h3>
            <p class="mt-2 text-2xl font-extrabold">TZS 30,000<span class="text-sm font-normal text-slate-400">/month</span></p>
        </div>
    </div>
</section>

<section class="bg-slate-900 py-16">
    <div class="mx-auto max-w-5xl rounded-2xl border border-amber-400/30 bg-slate-950 px-6 py-10 text-center sm:px-10">
        <h2 class="text-3xl font-bold">Ready to modernize your manufacturing business?</h2>
        <p class="mx-auto mt-3 max-w-2xl text-slate-300">Replace scattered tools with one reliable operating system for production, stock, and sales.</p>
        @auth
            <a href="{{ url('/admin') }}" class="mt-7 inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Open Dashboard</a>
        @else
            <a href="{{ url('/admin/login') }}" class="mt-7 inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Start Your Free Week</a>
        @endauth
    </div>
</section>
@endsection
