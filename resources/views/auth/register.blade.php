<x-guest-layout>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        <!-- Corporate Solution Branding -->
        <div class="mb-2 text-center">
            <div class="flex justify-center gap-5 items-center ">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-indigo-600 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 dark:shadow-indigo-950/40">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="">
                <h2 class="text-4xl font-bold bg-gradient-to-r from-slate-800 to-blue-700 bg-clip-text text-transparent">Corporate Solution</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">Create your account</p>
            </div>
            </div>
            
        </div>

        <!-- Registration Card - Wider & More Spacious -->
        <div
            class="w-full sm:max-w-2xl px-8 py-10 bg-white dark:bg-slate-800/90 shadow-2xl overflow-hidden sm:rounded-2xl border border-slate-200/80 dark:border-slate-700/80 transition-all duration-300">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name Field -->
                <div class="mb-6">
                    <x-input-label for="name" :value="__('Full Name')"
                        class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <x-text-input id="name"
                            class="block w-full pl-10 pr-3 py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                            placeholder="John Doe" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-sm" />
                </div>

                <!-- Email Field -->
                <div class="mb-6">
                    <x-input-label for="email" :value="__('Email Address')"
                        class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <x-text-input id="email"
                            class="block w-full pl-10 pr-3 py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                            type="email" name="email" :value="old('email')" required autocomplete="username"
                            placeholder="john@company.com" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-sm" />
                </div>

                <!-- Password & Confirm Password - Side by Side -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <!-- Password Field -->
                    <div>
                        <x-input-label for="password" :value="__('Password')"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <x-text-input id="password"
                                class="block w-full pl-10 pr-3 py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                                type="password" name="password" required autocomplete="new-password"
                                placeholder="Create password" />
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Minimum 8 characters</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')"
                            class="text-slate-700 dark:text-slate-200 font-semibold text-sm mb-1" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                            </div>
                            <x-text-input id="password_confirmation"
                                class="block w-full pl-10 pr-3 py-3.5 border-slate-300 dark:border-slate-600 dark:bg-slate-800/50 dark:text-white rounded-xl focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-all duration-200 text-base"
                                type="password" name="password_confirmation" required autocomplete="new-password"
                                placeholder="Confirm password" />
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-8 pt-2">
                    <a class="text-center sm:text-left text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors duration-200 inline-flex items-center justify-center"
                        href="{{ route('login') }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to login
                    </a>

                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 active:from-indigo-800 active:to-indigo-700 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-wide transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                            </path>
                        </svg>
                        {{ __('Create Account') }}
                    </button>
                </div>

                <!-- Terms & Footer -->
                <div class="mt-8 pt-5 border-t border-slate-200 dark:border-slate-700/70 text-center">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        By registering, you agree to our
                        <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Terms of Service</a>
                        and
                        <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Privacy Policy</a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-10 text-center text-xs text-slate-400 dark:text-slate-500">
            © {{ date('Y') }} Corporate Solution. All rights reserved.
        </div>
    </div>
</x-guest-layout>
