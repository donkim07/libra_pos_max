@extends('layouts.landing')

@section('title', 'Features | LibraPOS')
@section('meta_description', 'Explore manufacturing, inventory, sales, and analytics capabilities built for Tanzanian businesses.')
@section('og_title', 'Features | LibraPOS')
@section('og_description', 'From production batches to invoicing and reports, LibraPOS keeps operations connected.')

@section('content')
<section class="border-b border-slate-800 bg-grid">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
        <h1 class="text-4xl font-extrabold sm:text-5xl">Built for Real Manufacturing Operations</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-300">Every module is designed to keep your team aligned from raw materials to final sale, without operational blind spots.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl space-y-16 px-4 py-16 sm:px-6 lg:px-8">
    <article class="grid items-center gap-10 lg:grid-cols-2">
        <div>
            <h2 class="text-3xl font-bold text-amber-300">Manufacturing Management</h2>
            <ul class="mt-5 space-y-3 text-slate-200">
                <li>• Production batch creation and tracking</li>
                <li>• Raw material consumption visibility</li>
                <li>• Structured BOM and assembly workflows</li>
            </ul>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <div class="h-44 rounded-lg bg-slate-800"></div>
        </div>
    </article>

    <article class="grid items-center gap-10 lg:grid-cols-2">
        <div class="order-2 lg:order-1 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <div class="h-44 rounded-lg bg-slate-800"></div>
        </div>
        <div class="order-1 lg:order-2">
            <h2 class="text-3xl font-bold text-amber-300">Inventory & Warehousing</h2>
            <ul class="mt-5 space-y-3 text-slate-200">
                <li>• Multi-location stock management</li>
                <li>• Stock movement and adjustment logs</li>
                <li>• Low-stock visibility and bulk updates</li>
                <li>• SKU-driven item control with categories and units</li>
            </ul>
        </div>
    </article>

    <article class="grid items-center gap-10 lg:grid-cols-2">
        <div>
            <h2 class="text-3xl font-bold text-amber-300">Sales & Invoicing</h2>
            <ul class="mt-5 space-y-3 text-slate-200">
                <li>• POS sales terminal workflows</li>
                <li>• Bulk and wholesale order handling</li>
                <li>• Invoice generation and printable outputs</li>
                <li>• Customer, supplier, and order lifecycle control</li>
            </ul>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <div class="h-44 rounded-lg bg-slate-800"></div>
        </div>
    </article>

    <article class="grid items-center gap-10 lg:grid-cols-2">
        <div class="order-2 lg:order-1 rounded-2xl border border-slate-800 bg-slate-900 p-6">
            <div class="h-44 rounded-lg bg-slate-800"></div>
        </div>
        <div class="order-1 lg:order-2">
            <h2 class="text-3xl font-bold text-amber-300">Business Intelligence</h2>
            <ul class="mt-5 space-y-3 text-slate-200">
                <li>• Sales and production reporting dashboards</li>
                <li>• Cost vs revenue and expense awareness</li>
                <li>• Daily, weekly, and monthly summaries</li>
                <li>• Secure, isolated dashboards per tenant workspace</li>
            </ul>
        </div>
    </article>
</section>

<section class="border-t border-slate-800 bg-slate-900 py-16">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
        <h2 class="text-3xl font-bold">You can run operations with confidence</h2>
        <p class="mt-3 text-slate-300">From factory floor to cashier desk, LibraPOS gives one source of truth for your business.</p>
        <a href="/admin/login" class="mt-7 inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Start Free Trial</a>
    </div>
</section>
@endsection
