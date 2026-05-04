@extends('layouts.landing')

@section('title', 'About | LibraPOS')
@section('meta_description', 'Learn why LibraPOS was built to empower Tanzanian manufacturers with modern digital tools.')
@section('meta_keywords', 'about LibraPOS, Tanzanian software company, local-first POS, manufacturing digitization Tanzania')
@section('canonical_url', route('landing.about'))
@section('og_title', 'About | LibraPOS')
@section('og_description', 'Local-first POS and manufacturing software for reliable growth and better decisions.')
@section('og_url', route('landing.about'))

@section('content')
<section class="border-b border-slate-800 bg-grid">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold sm:text-5xl">Built for Tanzanian Manufacturers</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-300">Our mission is simple: give local businesses practical digital tools that improve control, speed, and confidence.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-2">
        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-7">
            <h2 class="text-2xl font-bold text-amber-300">Our Story</h2>
            <p class="mt-4 text-slate-300">We saw many manufacturers relying on manual records, disconnected spreadsheets, and delayed decision cycles. LibraPOS was created to unify production, inventory, sales, and reporting in one dependable platform.</p>
        </article>
        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-7">
            <h2 class="text-2xl font-bold text-amber-300">Our Mission</h2>
            <p class="mt-4 text-slate-300">Empower Tanzanian businesses to operate with clarity and discipline using software that is affordable, local-priced, and easy to adopt.</p>
        </article>
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <h3 class="font-bold">Reliability</h3>
            <p class="mt-2 text-sm text-slate-300">Stable workflows your team can trust every day.</p>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <h3 class="font-bold">Simplicity</h3>
            <p class="mt-2 text-sm text-slate-300">Powerful capabilities without unnecessary complexity.</p>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
            <h3 class="font-bold">Local-First</h3>
            <p class="mt-2 text-sm text-slate-300">TZS pricing and workflows grounded in local business realities.</p>
        </div>
    </div>
</section>

<section class="border-y border-slate-800 bg-slate-900/70">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold">Why teams stay with LibraPOS</h2>
        <p class="mt-3 max-w-3xl text-slate-300">We are not chasing feature lists for their own sake. The product exists so owners and floor teams can answer simple questions fast: what do we have, what did we make, what did we sell, and where did the money go?</p>
        <div class="mt-10 grid gap-6 md:grid-cols-3">
            <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-6">
                <h3 class="text-lg font-bold text-amber-300">Traceability you can defend</h3>
                <p class="mt-2 text-sm text-slate-300">Production, stock movements, and sales stay linked so you can explain variances and coach your team with facts, not guesses.</p>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-6">
                <h3 class="text-lg font-bold text-amber-300">Built for real Tanzanian operations</h3>
                <p class="mt-2 text-sm text-slate-300">Pricing in TZS, workflows that match how trading and manufacturing actually run here, and room to grow from one site to many without changing systems.</p>
            </div>
            <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-6">
                <h3 class="text-lg font-bold text-amber-300">A partnership mindset</h3>
                <p class="mt-2 text-sm text-slate-300">We invest in onboarding, honest limits per plan, and clear upgrades so your investment in process improvement compounds over time.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h2 class="text-3xl font-bold">Start your free trial today</h2>
        <p class="mt-3 text-slate-300">See how quickly your team can move from manual processes to predictable execution.</p>
        @auth
            <a href="{{ url('/admin') }}" class="mt-7 inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Open Dashboard</a>
        @else
            <a href="{{ url('/admin/login') }}" class="mt-7 inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Try LibraPOS Free</a>
        @endauth
    </div>
</section>
@endsection
