<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Auditoria
            </h2>

            <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800 transition">
                ← Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Filtros</h3>

                    <form method="GET" action="{{ route('audit.index') }}"
                        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                            <input type="number" name="user_id" id="user_id" value="{{ $filters['user_id'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="acao" class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
                            <input type="text" name="acao" id="acao" value="{{ $filters['acao'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="nivel" class="block text-sm font-medium text-gray-700 mb-1">Nível</label>
                            <select name="nivel" id="nivel"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">Todos</option>
                                <option value="info" @selected(($filters['nivel'] ?? '' )==='info' )>Info</option>
                                <option value="warning" @selected(($filters['nivel'] ?? '' )==='warning' )>Warning
                                </option>
                                <option value="error" @selected(($filters['nivel'] ?? '' )==='error' )>Error</option>
                            </select>
                        </div>

                        <div>
                            <label for="categoria"
                                class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                            <input type="text" name="categoria" id="categoria" value="{{ $filters['categoria'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="componente"
                                class="block text-sm font-medium text-gray-700 mb-1">Componente</label>
                            <input type="text" name="componente" id="componente"
                                value="{{ $filters['componente'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="tabela" class="block text-sm font-medium text-gray-700 mb-1">Tabela</label>
                            <input type="text" name="tabela" id="tabela" value="{{ $filters['tabela'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="inicio" class="block text-sm font-medium text-gray-700 mb-1">Data
                                inicial</label>
                            <input type="date" name="inicio" id="inicio" value="{{ $filters['inicio'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="fim" class="block text-sm font-medium text-gray-700 mb-1">Data final</label>
                            <input type="date" name="fim" id="fim" value="{{ $filters['fim'] ?? '' }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>

                        <div>
                            <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Por
                                página</label>
                            <select name="per_page" id="per_page"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="20" @selected(($filters['per_page'] ?? '20' )=='20' )>20</option>
                                <option value="50" @selected(($filters['per_page'] ?? '' )=='50' )>50</option>
                                <option value="100" @selected(($filters['per_page'] ?? '' )=='100' )>100</option>
                            </select>
                        </div>

                        <div class="xl:col-span-2 flex items-end gap-3">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                                Filtrar
                            </button>

                            <a href="{{ route('audit.index') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg transition">
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($logs as $log)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold shrink-0">
                                {{ strtoupper(substr($log->acao, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <p class="font-semibold text-gray-800">
                                        {{ $log->acao }}
                                    </p>

                                    @php
                                    $badgeClass = match($log->nivel) {
                                    'error' => 'bg-red-100 text-red-700',
                                    'warning' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-blue-100 text-blue-700',
                                    };
                                    @endphp

                                    <span class="text-xs font-medium px-3 py-1 rounded-full {{ $badgeClass }}">
                                        {{ strtoupper($log->nivel) }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-500 break-words">
                                    {{ $log->descricao ?: 'Sem descrição.' }}
                                </p>

                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                    <span><strong>ID:</strong> {{ $log->id }}</span>
                                    <span><strong>Usuário:</strong> {{ $log->user_name }} (#{{ $log->user_id }})</span>
                                    <span><strong>Categoria:</strong> {{ $log->categoria }}</span>
                                    <span><strong>Componente:</strong> {{ $log->componente }}</span>
                                    <span><strong>Tabela:</strong> {{ $log->tabela ?: '-' }}</span>
                                    <span><strong>Data:</strong> {{ optional($log->dt_criacao)->format('d/m/Y H:i:s')
                                        }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('audit.show', $log) }}"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium transition">
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 text-center">
                        <p class="text-gray-600 font-medium">Nenhum log encontrado.</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Quando houver eventos de auditoria, eles aparecerão aqui.
                        </p>
                    </div>
                </div>
                @endforelse
            </div>

            @if ($logs->hasPages())
            <div class="mt-6">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>