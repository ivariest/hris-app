@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 overflow-x-hidden bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="flex min-h-screen w-full flex-col justify-center overflow-hidden lg:flex-row dark:bg-gray-900">
            <div class="flex min-h-screen w-full flex-1 flex-col lg:w-1/2">
                <div class="mx-auto w-full max-w-md pt-6 sm:pt-10">
                    
                </div>

                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                    <div class="mb-8">
                        <img src="{{ asset('images/logo/standardpen-logo.png') }}" alt="StandardPen logo"
                            class="mb-6 h-12 w-auto object-contain" />
                        <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">Sign In</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Use your internal HRIS account to continue.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
                            Please check your email and password, then try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('signin.store') }}">
                        @csrf
                        <div class="space-y-5">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="email">
                                    Email<span class="text-error-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="admin@hris.local" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="password">
                                    Password<span class="text-error-500">*</span>
                                </label>
                                <input type="password" id="password" name="password" autocomplete="current-password" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Enter your password" />
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex cursor-pointer items-center text-sm font-normal text-gray-700 select-none dark:text-gray-400" for="remember">
                                    <input type="checkbox" id="remember" name="remember" value="1" class="mr-3 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                    Keep me logged in
                                </label>

                                <a href="{{ route('signup') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">
                                    Create account
                                </a>
                            </div>

                            <div>
                                <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                    Sign In
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <div class="bg-brand-950 relative hidden min-h-screen w-full items-center overflow-hidden lg:flex lg:w-1/2 dark:bg-white/5">
                <div class="z-1 flex items-center justify-center">
                    <x-common.common-grid-shape />
                </div>
            </div>
        </div>
    </div>
@endsection
