@props([
    'title' => 'Page',
    'description' => null,
])

<div class="space-y-6">
    <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="relative px-6 py-6 sm:px-8 lg:px-10">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50/80 via-transparent to-transparent dark:from-brand-500/10"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.28em] text-brand-500">
                        StandardPen HRIS
                    </p>
                    <h1 class="text-title-sm font-semibold text-gray-900 dark:text-white/90">
                        {{ $title }}
                    </h1>
                    @if ($description)
                        <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                @if (isset($actions) && trim((string) $actions) !== '')
                    <div class="flex flex-wrap items-center gap-3">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="space-y-6">
        {{ $slot }}
    </div>
</div>
