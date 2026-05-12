<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Auditoria #{{ $log->id }}
            </h2>

            <a href="{{ route('audit.index') }}" class="text-sm text-blue-600 hover:text-blue-800 transition">
                ← Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Informações gerais</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Data</p>
                            <p class="font-medium text-gray-800">{{ optional($log->dt_criacao)->format('d/m/Y H:i:s') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-gray-500">Usuário</p>
                            <p class="font-medium text-gray-800">{{ $log->user_name }} (#{{ $log->user_id }})</p>
                        </div>

                        <div>
                            <p class="text-gray-500">IP</p>
                            <p class="font-medium text-gray-800 break-all">{{ $log->ip }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Sessão</p>
                            <p class="font-medium text-gray-800 break-all">{{ $log->session_id ?: '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Ação</p>
                            <p class="font-medium text-gray-800">{{ $log->acao }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Nível</p>
                            <p class="font-medium text-gray-800">{{ strtoupper($log->nivel) }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Categoria</p>
                            <p class="font-medium text-gray-800">{{ $log->categoria }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Componente</p>
                            <p class="font-medium text-gray-800">{{ $log->componente }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Método HTTP</p>
                            <p class="font-medium text-gray-800">{{ $log->http_method }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Tabela</p>
                            <p class="font-medium text-gray-800">{{ $log->tabela ?: '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Registro ID</p>
                            <p class="font-medium text-gray-800">{{ $log->registro_id ?: '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">FK Referência</p>
                            <p class="font-medium text-gray-800">{{ $log->fk_referencia ?: '-' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Duração</p>
                            <p class="font-medium text-gray-800">{{ $log->duracao_ms ? $log->duracao_ms . ' ms' : '-' }}
                            </p>
                        </div>

                        <div class="md:col-span-2 xl:col-span-3">
                            <p class="text-gray-500">Request URI</p>
                            <p class="font-medium text-gray-800 break-all">{{ $log->request_uri ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Descrição</h3>

                    <div
                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 whitespace-pre-line">
                        {{ $log->descricao ?: 'Sem descrição.' }}
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">User Agent</h3>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 break-all">
                        {{ $log->user_agent ?: '-' }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Dados antes</h3>

                        <pre
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-700 overflow-x-auto">{{ json_encode($log->dados_antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Dados depois</h3>

                        <pre
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-xs text-gray-700 overflow-x-auto">{{ json_encode($log->dados_depois, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>