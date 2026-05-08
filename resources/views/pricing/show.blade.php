<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plano {{ $found['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow p-10 max-w-md w-full
        {{ $found['is_current'] ? 'border-2 border-blue-500' : 'border border-gray-200' }}">

        <a href="{{ route('pricing.index') }}" class="text-sm text-blue-500 hover:underline mb-6 inline-block">
            ← Voltar aos planos
        </a>

        @if($found['is_current'])
        <span class="text-xs font-semibold text-blue-500 uppercase tracking-wide block mb-2">Plano atual</span>
        @endif

        <h1 class="text-3xl font-bold text-gray-800">{{ $found['name'] }}</h1>

        <p class="text-5xl font-extrabold text-gray-900 mt-4">
            {{ $found['price_formatted'] }}
            @if($found['interval'])
            <span class="text-lg font-normal text-gray-500">/mês</span>
            @endif
        </p>

        @if($found['trial_days'] > 0)
        <p class="text-sm text-green-600 mt-2">{{ $found['trial_days'] }} dias grátis no primeiro período</p>
        @endif

        <ul class="mt-8 space-y-3">
            @foreach($found['features'] as $feature)
            <li class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ $feature }}
            </li>
            @endforeach
        </ul>

        <div class="mt-10">
            @if($found['cta_action'] && !$found['is_current'])
            <form method="POST" action="{{ route($found['cta_action']) }}">
                @csrf
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                    {{ $found['cta_label'] }}
                </button>
            </form>
            @else
            <button disabled class="w-full bg-gray-200 text-gray-500 font-semibold py-3 rounded-xl cursor-not-allowed">
                {{ $found['cta_label'] }}
            </button>
            @endif
        </div>
    </div>

</body>

</html>