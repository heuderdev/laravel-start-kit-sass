<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Convidar Equipe
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
                                class="w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">2</span>
                            <span class="text-sm font-medium text-gray-700">Equipe</span>
                        </div>
                        <div class="w-8 h-px bg-gray-300"></div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 text-sm font-bold flex items-center justify-center">3</span>
                            <span class="text-sm text-gray-400">Plano</span>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-1">Convide sua equipe</h3>
                    <p class="text-gray-500 text-sm mb-6">
                        Adicione membros ao workspace <strong>{{ $tenant->name }}</strong>.
                        Você pode pular e fazer isso depois.
                    </p>

                    <form method="POST" action="{{ route('invitations.invite') }}" class="mb-6">
                        @csrf

                        <div class="flex gap-3 flex-wrap">
                            <input type="email" name="email" placeholder="E-mail do convidado"
                                class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 @error('email') border-red-500 @enderror">
                            <select name="role"
                                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="admin">Admin</option>
                                <option value="member" selected>Member</option>
                            </select>
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                                Enviar convite
                            </button>
                        </div>
                        @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </form>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">

                        {{-- CORRIGIDO: era 'setup_workspace', deve ser 'setup_workspace' o step ATUAL desta view --}}
                        <form method="POST" action="{{ route('onboarding.skip', 'setup_workspace') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition">
                                Pular por agora
                            </button>
                        </form>

                        <form method="POST" action="{{ route('onboarding.skip', 'setup_workspace') }}">
                            @csrf
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg text-sm transition">
                                Continuar →
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>