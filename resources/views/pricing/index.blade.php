<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Escolha seu plano
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto w-full space-y-6 sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @foreach($plans as $plan)
                <div
                    class="flex flex-col rounded-2xl bg-white p-8 shadow {{ $plan->is_current ? 'border-2 border-blue-500' : 'border border-gray-200' }}">
                    @if($plan->is_current)
                    <span class="mb-2 text-xs font-semibold uppercase tracking-wide text-blue-500">
                        Plano atual
                    </span>
                    @endif

                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ $plan->name }}
                    </h2>

                    <p class="mt-3 text-4xl font-extrabold text-gray-900">
                        {{ $plan->price_formatted }}

                        @if($plan->interval)
                        <span class="text-base font-normal text-gray-500">
                            /mês
                        </span>
                        @endif
                    </p>

                    @if($plan->trial_days > 0)
                    <p class="mt-1 text-sm text-green-600">
                        {{ $plan->trial_days }} dias grátis
                    </p>
                    @endif

                    <ul class="mt-6 flex-1 space-y-2">
                        @foreach($plan->features as $feature)
                        <li class="flex items-center text-sm text-gray-600">
                            <svg class="mr-2 h-4 w-4 shrink-0 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>

                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if($plan->cta_action && ! $plan->is_current)
                        <form method="POST" action="{{ route($plan->cta_action) }}">
                            @csrf

                            @can('manageMembers', $tenant)
                            <button type="submit"
                                class="w-full rounded-xl bg-blue-600 py-3 font-semibold text-white transition hover:bg-blue-700">
                                {{ $plan->cta_label }}
                            </button>
                            @else
                            <div
                                class="w-full rounded-xl text-center bg-gray-200 py-3 font-semibold text-gray-500 transition">
                                Você não é proprietário desta empresa
                            </div>
                            @endcan
                        </form>
                        @else
                        <button disabled
                            class="w-full cursor-not-allowed rounded-xl bg-gray-200 py-3 font-semibold text-gray-500">
                            {{ $plan->cta_label }}
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>