<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Workspaces</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-2xl w-full">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Meus Workspaces</h1>
            <a href="{{ route('dashboard') }}" class="text-sm text-blue-500 hover:underline">← Dashboard</a>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <div class="space-y-4">
            @foreach($tenants as $tenant)
            <div class="bg-white rounded-2xl shadow p-6 flex items-center justify-between
                    {{ $tenant['is_default'] ? 'border-2 border-blue-500' : 'border border-gray-200' }}">

                <div class="flex items-center gap-4">
                    @if($tenant['logo_url'])
                    <img src="{{ $tenant['logo_url'] }}" alt="{{ $tenant['name'] }}"
                        class="w-12 h-12 rounded-full object-cover">
                    @else
                    <div
                        class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
                        {{ strtoupper(substr($tenant['name'], 0, 1)) }}
                    </div>
                    @endif

                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ $tenant['name'] }}
                            @if($tenant['is_default'])
                            <span
                                class="ml-2 text-xs font-medium text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full">Ativo</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ ucfirst($tenant['role']) }} &middot; Plano <span class="font-medium">{{
                                ucfirst($tenant['plan']) }}</span>
                        </p>
                    </div>
                </div>

                @if(!$tenant['is_default'])
                <form method="POST" action="{{ route('tenants.switch') }}">
                    @csrf
                    <input type="hidden" name="tenant_id" value="{{ $tenant['id'] }}">
                    <button type="submit"
                        class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                        Entrar
                    </button>
                </form>
                @else
                <span class="text-sm text-gray-400 font-medium">Workspace atual</span>
                @endif

            </div>
            @endforeach
        </div>

    </div>

</body>

</html>