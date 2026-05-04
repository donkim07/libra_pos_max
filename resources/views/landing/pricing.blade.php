@extends('layouts.landing')

@section('title', 'Pricing | LibraPOS')
@section('meta_description', 'Transparent TZS pricing for Tanzanian manufacturers. Start with a 7-day free trial.')
@section('og_title', 'Pricing | LibraPOS')
@section('og_description', 'Choose Starter, Business, or Enterprise with no-risk 7-day free trial.')

@section('content')
<section class="border-b border-slate-800 bg-grid">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold sm:text-5xl">Pricing That Grows With Your Operations</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-300">Clear monthly pricing in Tanzanian Shillings. No setup confusion. No hidden fees.</p>
    </div>
</section>

<section x-data="{ annual: false }" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="mb-8 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-center text-sm font-semibold text-emerald-300">
        All plans include a 7-day FREE trial — no credit card needed.
    </div>

    <div class="mb-10 flex items-center justify-center gap-4">
        <span :class="!annual ? 'text-white' : 'text-slate-400'" class="text-sm font-semibold">Monthly</span>
        <button @click="annual = !annual" class="relative h-8 w-16 rounded-full bg-slate-700 p-1" aria-label="Billing Toggle">
            <span :class="annual ? 'translate-x-8' : 'translate-x-0'" class="block h-6 w-6 rounded-full bg-amber-400 transition-transform"></span>
        </button>
        <span :class="annual ? 'text-white' : 'text-slate-400'" class="text-sm font-semibold">Annual <span class="text-emerald-300">(2 months free)</span></span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-7">
            <h2 class="text-2xl font-bold">Starter</h2>
            <p class="mt-1 text-sm text-slate-400">For focused teams starting digital operations.</p>
            <div class="mt-5">
                <p x-show="!annual" class="text-4xl font-extrabold">TZS 12,000<span class="text-sm font-medium text-slate-400">/month</span></p>
                <p x-show="annual" class="text-4xl font-extrabold">TZS 120,000<span class="text-sm font-medium text-slate-400">/year</span></p>
                <p x-show="annual" class="mt-1 text-sm text-slate-400"><span class="line-through">TZS 144,000</span> billed monthly equivalent</p>
            </div>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                <li>• 1 business location</li>
                <li>• Up to 500 products with SKU and category control</li>
                <li>• Core POS sales with invoice print workflows</li>
                <li>• Basic purchase and stock tracking to reduce guesswork</li>
                <li>• Ideal for building operational discipline early</li>
                <li>• 7-day free trial</li>
            </ul>
            <a href="/admin/login" class="mt-7 block rounded-lg border border-slate-600 px-4 py-3 text-center text-sm font-semibold hover:border-amber-300 hover:text-amber-300">Get Started Free</a>
        </article>

        <article class="relative rounded-2xl border-2 border-amber-400 bg-slate-900 p-7 shadow-xl shadow-amber-500/10">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-amber-400 px-4 py-1 text-xs font-bold text-slate-950">MOST POPULAR</span>
            <h2 class="text-2xl font-bold text-amber-300">Business</h2>
            <p class="mt-1 text-sm text-slate-300">For growing manufacturers running multiple workflows daily.</p>
            <div class="mt-5">
                <p x-show="!annual" class="text-4xl font-extrabold">TZS 22,000<span class="text-sm font-medium text-slate-400">/month</span></p>
                <p x-show="annual" class="text-4xl font-extrabold">TZS 220,000<span class="text-sm font-medium text-slate-400">/year</span></p>
                <p x-show="annual" class="mt-1 text-sm text-slate-400"><span class="line-through">TZS 264,000</span> billed monthly equivalent</p>
            </div>
            <ul class="mt-6 space-y-3 text-sm text-slate-100">
                <li>• 3 locations with synchronized inventory visibility</li>
                <li>• Unlimited products, suppliers, and customers</li>
                <li>• Full manufacturing and batch production tracking</li>
                <li>• Stock movement and stock adjustment controls</li>
                <li>• Sales orders, bulk sales flow, and stronger invoicing speed</li>
                <li>• Priority support for mission-critical operations</li>
                <li>• 7-day free trial</li>
            </ul>
            <a href="/admin/login" class="mt-7 block rounded-lg bg-amber-500 px-4 py-3 text-center text-sm font-bold text-slate-950 hover:bg-amber-400">Get Started Free</a>
        </article>

        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-7">
            <h2 class="text-2xl font-bold">Enterprise</h2>
            <p class="mt-1 text-sm text-slate-400">For high-volume operations that need depth and continuity.</p>
            <div class="mt-5">
                <p x-show="!annual" class="text-4xl font-extrabold">TZS 30,000<span class="text-sm font-medium text-slate-400">/month</span></p>
                <p x-show="annual" class="text-4xl font-extrabold">TZS 300,000<span class="text-sm font-medium text-slate-400">/year</span></p>
                <p x-show="annual" class="mt-1 text-sm text-slate-400"><span class="line-through">TZS 360,000</span> billed monthly equivalent</p>
            </div>
            <ul class="mt-6 space-y-3 text-sm text-slate-200">
                <li>• Unlimited locations and teams</li>
                <li>• Everything in Business, with advanced reporting depth</li>
                <li>• Financial visibility via expenses and transfer controls</li>
                <li>• Dedicated support with faster escalation</li>
                <li>• Custom onboarding for process alignment</li>
                <li>• Built for scale, auditability, and decision confidence</li>
                <li>• 7-day free trial</li>
            </ul>
            <a href="/admin/login" class="mt-7 block rounded-lg border border-slate-600 px-4 py-3 text-center text-sm font-semibold hover:border-amber-300 hover:text-amber-300">Get Started Free</a>
        </article>
    </div>
</section>

<section x-data="{ open: 0 }" class="border-t border-slate-800 bg-slate-900 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <h2 class="text-3xl font-bold">Frequently Asked Questions</h2>
        <div class="mt-8 space-y-3">
            <div class="rounded-xl border border-slate-700">
                <button @click="open = open === 1 ? 0 : 1" class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold">
                    <span>Does the free trial need a card?</span><span>+</span>
                </button>
                <p x-show="open === 1" class="px-5 pb-5 text-sm text-slate-300">No. You can explore the full system for 7 days with no credit card required.</p>
            </div>
            <div class="rounded-xl border border-slate-700">
                <button @click="open = open === 2 ? 0 : 2" class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold">
                    <span>Can I move from Starter to Business later?</span><span>+</span>
                </button>
                <p x-show="open === 2" class="px-5 pb-5 text-sm text-slate-300">Yes. Upgrade anytime as your locations, products, and production complexity grow.</p>
            </div>
            <div class="rounded-xl border border-slate-700">
                <button @click="open = open === 3 ? 0 : 3" class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold">
                    <span>Is each business tenant isolated?</span><span>+</span>
                </button>
                <p x-show="open === 3" class="px-5 pb-5 text-sm text-slate-300">Yes. Every business runs in its own isolated workspace with separate operational data.</p>
            </div>
            <div class="rounded-xl border border-slate-700">
                <button @click="open = open === 4 ? 0 : 4" class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold">
                    <span>Do plans include manufacturing workflows?</span><span>+</span>
                </button>
                <p x-show="open === 4" class="px-5 pb-5 text-sm text-slate-300">Business and Enterprise include complete manufacturing and batch tracking capabilities.</p>
            </div>
            <div class="rounded-xl border border-slate-700">
                <button @click="open = open === 5 ? 0 : 5" class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold">
                    <span>What currency is used for billing?</span><span>+</span>
                </button>
                <p x-show="open === 5" class="px-5 pb-5 text-sm text-slate-300">All pricing is in Tanzanian Shillings (TZS), designed for local business realities.</p>
            </div>
            <div class="rounded-xl border border-slate-700">
                <button @click="open = open === 6 ? 0 : 6" class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold">
                    <span>How fast can we be operational?</span><span>+</span>
                </button>
                <p x-show="open === 6" class="px-5 pb-5 text-sm text-slate-300">Most teams can begin basic usage within hours. Enterprise includes custom onboarding support.</p>
            </div>
        </div>
        <div class="mt-10 text-center">
            <a href="/admin/login" class="inline-block rounded-lg bg-amber-500 px-6 py-3 font-bold text-slate-950 hover:bg-amber-400">Get Started Free</a>
        </div>
    </div>
</section>
@endsection
