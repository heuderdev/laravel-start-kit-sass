<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Tenant #{{ $tenant->id }} - {{ $tenant->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="bg-red-100 text-red-800 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="space-y-2 mb-6">
                    <p><strong>ID:</strong> {{ $tenant->id }}</p>
                    <p><strong>Nome:</strong> {{ $tenant->name }}</p>
                    <p><strong>Slug:</strong> {{ $tenant->slug }}</p>
                    <p><strong>Plano:</strong> {{ $tenant->plan ?? 'free' }}</p>
                </div>

                <form method="POST" action="{{ route('admin.tenants.bypass.update', $tenant) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="bypass_plan_limits" value="0">
                            <input type="checkbox" name="bypass_plan_limits" value="1"
                                @checked(old('bypass_plan_limits', $tenant->bypass_plan_limits))
                            >
                            <span>Permitir bypass de plano para este tenant</span>
                        </label>
                    </div>

                    <div>
                        <label for="bypass_plan_limits_data_limite" class="block text-sm font-medium text-gray-700">
                            Data limite do bypass
                        </label>
                        <input id="bypass_plan_limits_data_limite" type="datetime-local"
                            name="bypass_plan_limits_data_limite"
                            value="{{ old('bypass_plan_limits_data_limite', $tenant->bypass_plan_limits_data_limite?->format('Y-m-d\\TH:i')) }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <p class="mt-1 text-sm text-gray-500">
                            Deixe vazio somente se quiser bypass sem data final.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                            Salvar
                        </button>

                        <a href="{{ route('admin.tenants.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">
                            Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>