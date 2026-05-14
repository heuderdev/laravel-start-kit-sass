<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Escolher Plano
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <x-flash-messages />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">

                    {{-- Indicador de steps --}}
                    <div class="flex items-center justify-center gap-2 mb-8">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-400 line-through">Workspace</span>
                        </div>
                        <div class="w-8 h-px bg-green-400"></div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-400 line-through">Equipe</span>
                        </div>
                        <div class="w-8 h-px bg-green-400"></div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">3</span>
                            <span class="text-sm font-medium text-gray-700">Plano</span>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-1">Escolha seu plano</h3>
                    <p class="text-gray-500 text-sm mb-6">
                        Selecione o plano ideal para <strong>{{ $tenant->name }}</strong>.
                        Você pode alterar a qualquer momento.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

                        {{-- Plano Free --}}
                        <div
                            class="border-2 {{ $tenant->plan === 'free' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} rounded-lg p-5 hover:border-blue-400 transition">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-base font-bold text-gray-800">Free</span>
                                @if($tenant->plan === 'free')
                                <span
                                    class="text-xs bg-blue-100 text-blue-700 font-medium px-2 py-0.5 rounded-full">Plano
                                    atual</span>
                                @endif
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                R$ 0<span class="text-sm font-normal text-gray-500">/mês</span>
                            </p>
                            <ul class="text-sm text-gray-600 space-y-1 mt-3">
                                <li>✓ Até 3 membros</li>
                                <li>✓ Funcionalidades básicas</li>
                                <li>✗ Suporte prioritário</li>
                            </ul>
                            {{-- CORRIGIDO: step atual desta view é 'invite_team' --}}
                            <form method="POST" action="{{ route('onboarding.skip', 'invite_team') }}" class="mt-4">
                                @csrf
                                <button type="submit"
                                    class="w-full border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium py-2 rounded-lg transition">
                                    Continuar com Free
                                </button>
                            </form>
                        </div>

                        {{-- Plano Pro --}}
                        <div
                            class="border-2 {{ $tenant->plan === 'pro' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }} rounded-lg p-5 hover:border-blue-400 transition relative">
                            <span
                                class="absolute -top-3 left-4 text-xs bg-yellow-400 text-yellow-900 font-bold px-3 py-0.5 rounded-full">
                                Recomendado
                            </span>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-base font-bold text-gray-800">Pro</span>
                                @if($tenant->plan === 'pro')
                                <span
                                    class="text-xs bg-blue-100 text-blue-700 font-medium px-2 py-0.5 rounded-full">Plano
                                    atual</span>
                                @endif
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                R$ 97<span class="text-sm font-normal text-gray-500">/mês</span>
                            </p>
                            <ul class="text-sm text-gray-600 space-y-1 mt-3">
                                <li>✓ Membros ilimitados</li>
                                <li>✓ Todas as funcionalidades</li>
                                <li>✓ Suporte prioritário</li>
                            </ul>
                            <a href="{{ route('pricing.index') }}"
                                class="block mt-4 w-full text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition">
                                Assinar Pro
                            </a>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>