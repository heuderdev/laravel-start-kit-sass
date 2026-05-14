<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Bem-vindo ao seu workspace
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <x-flash-messages />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-center">

                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Vamos configurar seu workspace!</h3>
                    <p class="text-gray-500 mb-8">Leva menos de 2 minutos para deixar tudo pronto.</p>

                    {{-- Indicador de steps --}}
                    <div class="flex items-center justify-center gap-2 mb-8">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">1</span>
                            <span class="text-sm font-medium text-gray-700">Workspace</span>
                        </div>
                        <div class="w-8 h-px bg-gray-300"></div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 text-sm font-bold flex items-center justify-center">2</span>
                            <span class="text-sm text-gray-400">Equipe</span>
                        </div>
                        <div class="w-8 h-px bg-gray-300"></div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 text-sm font-bold flex items-center justify-center">3</span>
                            <span class="text-sm text-gray-400">Plano</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('onboarding.setup-workspace') }}">
                        @csrf

                        <div class="text-left mb-6">
                            <label for="workspace_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Como se chama sua empresa ou projeto?
                            </label>
                            <input type="text" id="workspace_name" name="workspace_name"
                                value="{{ old('workspace_name', $tenant->name) }}" placeholder="Ex: Minha Empresa LTDA"
                                required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 @error('workspace_name') border-red-500 @enderror">
                            @error('workspace_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition">
                            Continuar →
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>