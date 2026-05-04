@extends('layouts.landing')

@section('title', 'About | LibraPOS')
@section('meta_description', 'Learn why LibraPOS was built to empower Tanzanian manufacturers with modern digital tools.')
@section('og_title', 'About | LibraPOS')
@section('og_description', 'Local-first POS and manufacturing software for reliable growth and better decisions.')

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
        <h2 class="text-3xl font-bold">Team</h2>
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <div class="rounded-xl border border-slate-800 p-6 text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-slate-700"></div>
                <h3 class="mt-4 font-bold">Product Lead</h3>
                <p class="text-sm text-slate-400">Operations & product strategy</p>
            </div>
            <div class="rounded-xl border border-slate-800 p-6 text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-slate-700"></div>
                <h3 class="mt-4 font-bold">Engineering Lead</h3>
                <p class="text-sm text-slate-400">Platform and reliability</p>
            </div>
            <div class="rounded-xl border border-slate-800 p-6 text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-slate-700"></div>
                <h3 class="mt-4 font-bold">Customer Success</h3>
                <p class="text-sm text-slate-400">Onboarding and growth support</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h2 class="text-3xl font-bold">Start your free trial today</h2>
        <p class="mt-3 text-slate-300">See how quickly your team can move from manual processes to predictable execution.</p>
        <a href="/admin/login" class="mt-7 inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Try LibraPOS Free</a>
    </div>
</section>
@endsection
