<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Membros de {{ $tenant->name }}
            </h2>

            <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800 transition">
                ← Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @foreach (['success', 'error', 'info'] as $flash)
            @if (session($flash))
            <div class="mb-4 px-4 py-3 rounded-lg border
                        {{ $flash === 'success' ? 'bg-green-50 border-green-400 text-green-800' : '' }}
                        {{ $flash === 'error' ? 'bg-red-50 border-red-400 text-red-800' : '' }}
                        {{ $flash === 'info' ? 'bg-blue-50 border-blue-400 text-blue-800' : '' }}">
                {{ session($flash) }}
            </div>
            @endif
            @endforeach

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Convidar membro</h3>

                    <form method="POST" action="{{ route('invitations.invite') }}" class="flex gap-3 flex-wrap">
                        @csrf

                        <input type="email" name="email" placeholder="E-mail do convidado" required
                            class="flex-1 min-w-[220px] border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">

                        <select name="role"
                            class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                            <option value="funcionario">Funcionário</option>
                            <option value="cliente">Cliente</option>
                        </select>

                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                            Enviar convite
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($members as $member)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>

                            <div>
                                <p class="font-semibold text-gray-800">{{ $member->name }}</p>
                                <p class="text-sm text-gray-500">{{ $member->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span
                                class="text-xs font-medium px-3 py-1 rounded-full
                                    {{ $member->pivot->role === 'owner' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($member->pivot->role) }}
                            </span>

                            @if ($member->pivot->role !== 'owner')
                            <form method="POST" action="{{ route('members.update', $member->id) }}">
                                @csrf
                                @method('PATCH')

                                <select name="role" onchange="this.form.submit()"
                                    class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>
                                        Member
                                    </option>
                                    <option value="admin" {{ $member->pivot->role === 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                    <option value="funcionario" {{ $member->pivot->role === 'funcionario' ? 'selected' :
                                        '' }}>
                                        Funcionário
                                    </option>
                                    <option value="cliente" {{ $member->pivot->role === 'cliente' ? 'selected' : '' }}>
                                        Cliente
                                    </option>
                                </select>
                            </form>

                            <form method="POST" action="{{ route('members.destroy', $member->id) }}"
                                onsubmit="return confirm('Remover {{ $member->name }} do workspace?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="text-sm text-red-500 hover:text-red-700 font-medium transition">
                                    Remover
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>