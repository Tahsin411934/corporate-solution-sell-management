<x-guest-layout>
    <div class="min-h-screen bg-slate-950 flex items-center justify-center px-4 py-10 relative overflow-hidden">
        <div class="absolute -top-40 -right-32 w-96 h-96 rounded-full bg-blue-600/20 blur-3xl"></div>
        <div class="absolute -bottom-48 -left-32 w-[28rem] h-[28rem] rounded-full bg-cyan-500/10 blur-3xl"></div>
        <div class="relative w-full max-w-5xl grid lg:grid-cols-2 bg-white rounded-3xl shadow-2xl overflow-hidden">
            <section class="hidden lg:flex bg-blue-950 p-12 text-white flex-col justify-between min-h-[620px] relative overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ asset('aboutus.jpg') }}');"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-blue-950/95 via-blue-950/80 to-slate-950/95"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('logo.png') }}" alt="Corporate Solution" class="w-11 h-11 rounded-xl object-contain bg-white p-1 shadow-lg shadow-blue-500/30">
                        <div><h1 class="text-xl font-bold tracking-tight">Corporate Solution</h1><p class="text-xs text-blue-200">Professional Billing</p></div>
                    </div>
                    <div class="mt-24 max-w-sm">
                        <p class="text-blue-300 text-sm font-semibold uppercase tracking-[0.2em]">Invoice management made simple</p>
                        <h2 class="mt-5 text-4xl font-bold leading-tight">Create, print and track every invoice with confidence.</h2>
                        <p class="mt-6 text-slate-300 leading-relaxed">Manage professional bills, customer payments and outstanding balances from one secure workspace.</p>
                    </div>
                </div>
                <div class="relative z-10 grid grid-cols-3 gap-4 border-t border-white/10 pt-6 text-sm">
                    <div><p class="text-2xl font-bold">A4</p><p class="text-slate-400 mt-1">Print-ready bills</p></div>
                    <div><p class="text-2xl font-bold">PDF</p><p class="text-slate-400 mt-1">Instant downloads</p></div>
                    <div><p class="text-2xl font-bold">24/7</p><p class="text-slate-400 mt-1">Easy access</p></div>
                </div>
            </section>
            <section class="p-7 sm:p-12 flex flex-col justify-center">
                <div class="lg:hidden flex items-center gap-3 mb-10">
                    <img src="{{ asset('logo.png') }}" alt="Corporate Solution" class="w-11 h-11 rounded-xl object-contain bg-white p-1 shadow-md">
                    <div><h1 class="text-xl font-bold text-slate-900">Corporate Solution</h1><p class="text-xs text-slate-500">Professional Billing</p></div>
                </div>
                <div class="max-w-md mx-auto w-full">
                    <div class="mb-8"><p class="text-sm font-semibold text-blue-600">Welcome back</p><h2 class="mt-2 text-3xl font-bold text-slate-900">Sign in to your account</h2><p class="mt-2 text-slate-500">Access your invoices and payment workspace.</p></div>
                    <x-auth-session-status class="mb-6" :status="session('status')" />
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div>
                            <x-input-label for="email" :value="__('Email address')" class="text-sm font-semibold text-slate-700" />
                            <div class="relative mt-2"><i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i><x-text-input id="email" class="block w-full rounded-xl border-slate-200 pl-11 py-3.5 focus:border-blue-500 focus:ring-blue-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" /></div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between"><x-input-label for="password" :value="__('Password')" class="text-sm font-semibold text-slate-700" />@if (Route::has('password.request'))<a class="text-sm font-semibold text-blue-600 hover:text-blue-800" href="{{ route('password.request') }}">Forgot password?</a>@endif</div>
                            <div class="relative mt-2">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <x-text-input id="password" class="block w-full rounded-xl border-slate-200 pl-11 pr-12 py-3.5 focus:border-blue-500 focus:ring-blue-500" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
                                <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" aria-label="Show password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>
                        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600"><input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span>{{ __('Remember me') }}</span></label>
                        <button type="submit" class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"><i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>{{ __('Sign in') }}</button>
                    </form>
                    @if (Route::has('register'))<p class="mt-8 text-center text-sm text-slate-500">Need an account? <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-800">Create one</a></p>@endif
                    <p class="mt-10 text-center text-xs text-slate-400">© {{ date('Y') }} Corporate Solution. All rights reserved.</p>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        const icon = toggle?.querySelector('i');

        toggle?.addEventListener('click', function () {
            const isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    });
</script>
