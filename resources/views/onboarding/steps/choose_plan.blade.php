<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tudo pronto!
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <x-flash-messages />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-center">

                    {{-- Todos os steps completos --}}
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
                                class="w-8 h-8 rounded-full bg-green-500 text-white text-sm font-bold flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-400 line-through">Plano</span>
                        </div>
                    </div>

                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        Workspace configurado com sucesso!
                    </h3>
                    <p class="text-gray-500 mb-8">
                        Bem-vindo ao <strong>{{ $tenant->name }}</strong>. Seu ambiente está pronto para uso.
                    </p>

                    {{-- CORRETO: step atual é 'choose_plan' → avança para 'completed' --}}
                    <form method="POST" action="{{ route('onboarding.skip', 'choose_plan') }}">
                        @csrf
                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition text-base">
                            Ir para o Dashboard →
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>