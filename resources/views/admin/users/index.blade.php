<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Administração de Super Admins
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gerencie quais usuários podem acessar o painel global administrativo.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.users.index') }}"
                        class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_auto]">
                        <div>
                            <label for="search" class="mb-2 block text-sm font-medium text-gray-700">
                                Buscar usuário
                            </label>
                            <input id="search" name="search" type="text" value="{{ $filters['search'] ?? '' }}"
                                placeholder="Busque por ID, nome ou e-mail"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    ID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Nome
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    E-mail
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Roles
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Ações
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($users as $user)
                            @php
                            $isSuperAdmin = $user->roles->contains('name', 'super-admin');
                            @endphp

                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                    {{ $user->id }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Criado em {{ $user->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $user->email }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($user->roles as $role)
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                            {{ $role->name }}
                                        </span>
                                        @empty
                                        <span class="text-xs text-gray-400">
                                            Sem roles
                                        </span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        @if (! $isSuperAdmin)
                                        <form method="POST"
                                            action="{{ route('admin.users.promote-super-admin', $user) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                                Tornar super-admin
                                            </button>
                                        </form>
                                        @else
                                        <form method="POST"
                                            action="{{ route('admin.users.revoke-super-admin', $user) }}">
                                            @csrf
                                            @method('DELETE')

                                            @if($user->email === config('services.super_admin.email'))
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg cursor-not-allowed bg-gray-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                @disabled($user->email === config('services.super_admin.email'))
                                                >
                                                super-admin
                                            </button>
                                            @else
                                            <button type="submit"
                                                @class([ 'inline-flex items-center rounded-lg px-3 py-2 text-xs font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2'
                                                , 'bg-gray-600 hover:bg-gray-700 cursor-not-allowed'=> auth()->id() ===
                                                $user->id,
                                                'bg-red-600 hover:bg-red-700' => auth()->id() !== $user->id,
                                                ])
                                                @disabled(auth()->id() === $user->id)
                                                >
                                                Remover super-admin
                                            </button>
                                            @endif
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($users, 'links'))
                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>