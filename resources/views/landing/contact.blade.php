@extends('layouts.landing')

@section('title', 'Contact | LibraPOS')
@section('meta_description', 'Talk to LibraPOS about plans, onboarding, and manufacturing operations support.')
@section('og_title', 'Contact | LibraPOS')
@section('og_description', 'Reach out to the LibraPOS team in Dar es Salaam for fast support and onboarding guidance.')

@section('content')
<section class="border-b border-slate-800 bg-grid">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold sm:text-5xl">Contact Us</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-300">Tell us about your business and we will help you choose the best setup.</p>
    </div>
</section>

<section class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-3 lg:px-8">
    <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900 p-6">
        @if (session('success'))
            <div class="mb-5 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
        @endif
        <form action="{{ route('landing.contact.store') }}" method="POST" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <input name="name" value="{{ old('name') }}" class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm" placeholder="Name" required>
            <input name="business_name" value="{{ old('business_name') }}" class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm" placeholder="Business Name" required>
            <input type="email" name="email" value="{{ old('email') }}" class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm" placeholder="Email" required>
            <input name="phone" value="{{ old('phone') }}" class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm" placeholder="Phone" required>
            <select name="plan_interest" class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm sm:col-span-2" required>
                <option value="">Plan of Interest</option>
                <option value="starter" @selected(old('plan_interest') === 'starter')>Starter</option>
                <option value="business" @selected(old('plan_interest') === 'business')>Business</option>
                <option value="enterprise" @selected(old('plan_interest') === 'enterprise')>Enterprise</option>
            </select>
            <textarea name="message" rows="5" class="rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm sm:col-span-2" placeholder="Message" required>{{ old('message') }}</textarea>
            <button class="rounded-lg bg-amber-500 px-5 py-3 text-sm font-bold text-slate-950 hover:bg-amber-400 sm:col-span-2">Submit</button>
        </form>
    </div>

    <aside class="space-y-5">
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-5">
            <h3 class="font-bold">Contact Info</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-300">
                <li>Email: librapos@rejoda.co.tz</li>
                <li>WhatsApp: +255748224536</li>
                <li>Location: Dar es Salaam, Tanzania</li>
            </ul>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-5">
            <h3 class="font-bold">Location</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-300">Dar es Salaam, Tanzania</p>
        </div>
        <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm font-semibold text-emerald-300">
            Response within 24 hours
        </div>
    </aside>
</section>
@endsection
