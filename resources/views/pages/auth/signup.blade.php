@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 overflow-x-hidden bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="flex min-h-screen w-full flex-col justify-center overflow-hidden lg:flex-row dark:bg-gray-900">
            <div class="flex min-h-screen w-full flex-1 flex-col lg:w-1/2">
                <div class="mx-auto w-full max-w-md pt-6 sm:pt-10">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Back to home
                    </a>
                </div>

                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                    <div class="mb-8">
                        <img src="{{ asset('images/logo/standardpen-logo.png') }}" alt="StandardPen logo"
                            class="mb-6 h-12 w-auto object-contain" />
                        <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">Create account</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Set up an internal HRIS account.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">
                            Please check the form fields and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('signup.store') }}">
                        @csrf
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="first_name">
                                        First name<span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        placeholder="John" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="last_name">
                                        Last name<span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                        placeholder="Doe" />
                                </div>
                            </div>

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
                                <input type="password" id="password" name="password" autocomplete="new-password" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Minimum 8 characters" />
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="password_confirmation">
                                    Confirm password<span class="text-error-500">*</span>
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Repeat password" />
                            </div>

                            <label class="flex cursor-pointer items-start text-sm font-normal text-gray-700 select-none dark:text-gray-400" for="terms">
                                <input type="checkbox" id="terms" name="terms" value="1" class="mr-3 mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                <span>I agree to the internal HRIS account terms and data usage policy.</span>
                            </label>

                            <div>
                                <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                    Sign Up
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-5">
                        <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400">
                            Already have an account?
                            <a href="{{ route('signin') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign In</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-brand-950 relative hidden min-h-screen w-full items-center overflow-hidden lg:flex lg:w-1/2 dark:bg-white/5">
                <div class="z-1 flex items-center justify-center">
                    <x-common.common-grid-shape />
                    <div class="flex max-w-xs flex-col items-center">
                        <a href="{{ url('/') }}" class="mb-4 block">
                            <img src="{{ asset('images/logo/standardpen-logo.png') }}" alt="StandardPen logo"
                                class="h-14 w-auto object-contain" />
                        </a>
                        <p class="text-center text-gray-400 dark:text-white/60">
                            Create a clean internal account for the HRIS team.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
