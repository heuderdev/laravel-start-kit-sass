<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gerenciador de Cookies
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Criar / Atualizar Cookie
                    </h3>

                    <form method="POST" action="{{ route('cookies.store') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <label for="theme" class="mb-2 block text-sm font-medium text-gray-700">
                                Tema
                            </label>
                            <select id="theme" name="theme"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="system" @selected(old('theme', $cookieData['theme'] ?? 'system'
                                    )==='system' )>System</option>
                                <option value="light" @selected(old('theme', $cookieData['theme'] ?? '' )==='light' )>
                                    Light</option>
                                <option value="dark" @selected(old('theme', $cookieData['theme'] ?? '' )==='dark' )>Dark
                                </option>
                            </select>
                            @error('theme')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="locale" class="mb-2 block text-sm font-medium text-gray-700">
                                Locale
                            </label>
                            <input id="locale" name="locale" type="text" maxlength="2"
                                value="{{ old('locale', $cookieData['locale'] ?? 'pt') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('locale')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tenant_id" class="mb-2 block text-sm font-medium text-gray-700">
                                Tenant ID
                            </label>
                            <input id="tenant_id" name="tenant_id" type="number"
                                value="{{ old('tenant_id', $cookieData['tenant_id'] ?? '') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('tenant_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3">
                            <input id="remember_workspace" name="remember_workspace" type="hidden" value="0">
                            <input id="remember_workspace" name="remember_workspace" type="checkbox" value="1"
                                @checked(old('remember_workspace', $cookieData['remember_workspace'] ?? false))
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="remember_workspace" class="text-sm font-medium text-gray-700">
                                Lembrar workspace por 30 dias
                            </label>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                                Salvar cookie
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Estado atual
                    </h3>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-sm text-gray-500">Cookie existe</p>
                            <p class="mt-1 font-semibold text-gray-800">
                                {{ $cookieExists ? 'Sim' : 'Não' }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-sm text-gray-500">Nome</p>
                            <p class="mt-1 font-semibold text-gray-800">
                                {{ $cookieName }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-sm text-gray-500">Conteúdo decodificado</p>
                            <pre
                                class="mt-2 overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-green-400">{{ json_encode($cookieData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('cookies.renew') }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                                    Renovar 30 dias
                                </button>
                            </form>

                            <form method="POST" action="{{ route('cookies.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                                    Remover cookie
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>