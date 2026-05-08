<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Administração de Tenants
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="GET" action="{{ route('admin.tenants.index') }}" class="flex gap-3">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Buscar por id, nome ou slug" class="w-full border-gray-300 rounded-md shadow-sm">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">ID</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nome</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Slug</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Plano</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Bypass</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Data limite</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($tenants as $tenant)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $tenant->id }}</td>
                            <td class="px-4 py-3 text-sm">{{ $tenant->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $tenant->slug }}</td>
                            <td class="px-4 py-3 text-sm">{{ $tenant->plan ?? 'free' }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ $tenant->bypass_plan_limits ? 'Sim' : 'Não' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $tenant->bypass_plan_limits_data_limite?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.tenants.edit', $tenant) }}"
                                    class="text-indigo-600 hover:text-indigo-800">
                                    Editar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                Nenhum tenant encontrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="p-4">
                    {{ $tenants->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>