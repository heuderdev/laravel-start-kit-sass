<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Plano {{ $found['name'] }}
            </h2>

            <a href="{{ route('pricing.index') }}" class="text-sm text-blue-500 hover:underline">
                ← Voltar aos planos
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto w-full max-w-md sm:px-6 lg:px-8">
            <div
                class="rounded-2xl bg-white p-10 shadow {{ $found['is_current'] ? 'border-2 border-blue-500' : 'border border-gray-200' }}">
                @if($found['is_current'])
                <span class="mb-2 block text-xs font-semibold uppercase tracking-wide text-blue-500">
                    Plano atual
                </span>
                @endif

                <h1 class="text-3xl font-bold text-gray-800">
                    {{ $found['name'] }}
                </h1>

                <p class="mt-4 text-5xl font-extrabold text-gray-900">
                    {{ $found['price_formatted'] }}

                    @if($found['interval'])
                    <span class="text-lg font-normal text-gray-500">
                        /mês
                    </span>
                    @endif
                </p>

                @if($found['trial_days'] > 0)
                <p class="mt-2 text-sm text-green-600">
                    {{ $found['trial_days'] }} dias grátis no primeiro período
                </p>
                @endif

                <ul class="mt-8 space-y-3">
                    @foreach($found['features'] as $feature)
                    <li class="flex items-center text-gray-600">
                        <svg class="mr-3 h-5 w-5 shrink-0 text-green-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>

                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>

                <div class="mt-10">
                    @if($found['cta_action'] && ! $found['is_current'])
                    <form method="POST" action="{{ route($found['cta_action']) }}">
                        @csrf

                        <button type="submit"
                            class="w-full rounded-xl bg-blue-600 py-3 font-semibold text-white transition hover:bg-blue-700">
                            {{ $found['cta_label'] }}
                        </button>
                    </form>
                    @else
                    <button disabled
                        class="w-full cursor-not-allowed rounded-xl bg-gray-200 py-3 font-semibold text-gray-500">
                        {{ $found['cta_label'] }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>