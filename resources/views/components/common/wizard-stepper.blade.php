@props([
    'steps' => [],
    'stepModel' => 'step',
])

<div class="rounded-2xl border border-gray-200 bg-white px-5 py-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:px-8">
    <div class="overflow-x-auto pb-1">
        <div class="relative mx-auto min-w-[760px] px-6 pt-1">
            <div class="absolute left-[82px] right-[82px] top-[18px] h-[5px] overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div
                    class="h-full rounded-full bg-success-500 transition-all duration-300"
                    :style="`width: ${(({{ $stepModel }} - 1) / {{ max(count($steps) - 1, 1) }}) * 100}%`"
                ></div>
            </div>

            <div class="relative z-10 flex items-start justify-between">
            @foreach ($steps as $index => $step)
                @php
                    $stepNumber = $index + 1;
                @endphp

                <div class="flex w-[124px] shrink-0 flex-col items-center text-center">
                    <button
                        type="button"
                        @click="{{ $stepModel }} = {{ $stepNumber }}"
                        class="group flex flex-col items-center text-center"
                    >
                        <div
                            class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 text-xs font-semibold leading-none shadow-sm transition"
                            :class="{{ $stepModel }} > {{ $stepNumber }}
                                ? 'border-success-500 bg-success-500 text-white'
                                : ({{ $stepModel }} === {{ $stepNumber }}
                                    ? 'border-brand-500 bg-brand-500 text-white ring-4 ring-brand-500/10'
                                    : 'border-gray-300 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400')"
                        >
                            <svg x-show="{{ $stepModel }} > {{ $stepNumber }}" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M5 10.5L8.5 14L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span x-show="{{ $stepModel }} <= {{ $stepNumber }}">{{ $stepNumber }}</span>
                        </div>

                        <div
                            class="mt-2 min-h-[36px] text-sm font-medium leading-5 transition"
                            :class="{{ $stepModel }} === {{ $stepNumber }}
                                ? 'text-gray-900 dark:text-white/90'
                                : 'text-gray-600 dark:text-gray-400'"
                        >
                            {{ $step }}
                        </div>
                    </button>
                </div>
            @endforeach
            </div>
        </div>
    </div>
</div>
