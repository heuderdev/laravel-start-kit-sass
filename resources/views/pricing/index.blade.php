<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-4xl w-full">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-10">Escolha seu plano</h1>

        @if(session('warning'))
        <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded text-center">
            {{ session('warning') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($plans as $plan)
            <div class="bg-white rounded-2xl shadow p-8 flex flex-col
                    {{ $plan['is_current'] ? 'border-2 border-blue-500' : 'border border-gray-200' }}">

                @if($plan['is_current'])
                <span class="text-xs font-semibold text-blue-500 uppercase tracking-wide mb-2">Plano atual</span>
                @endif

                <h2 class="text-2xl font-bold text-gray-800">{{ $plan['name'] }}</h2>

                <p class="text-4xl font-extrabold text-gray-900 mt-3">
                    {{ $plan['price_formatted'] }}
                    @if($plan['interval'])
                    <span class="text-base font-normal text-gray-500">/mês</span>
                    @endif
                </p>

                @if($plan['trial_days'] > 0)
                <p class="text-sm text-green-600 mt-1">{{ $plan['trial_days'] }} dias grátis</p>
                @endif

                <ul class="mt-6 space-y-2 flex-1">
                    @foreach($plan['features'] as $feature)
                    <li class="flex items-center text-gray-600 text-sm">
                        <svg class="w-4 h-4 text-green-500 mr-2 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    @if($plan['cta_action'] && !$plan['is_current'])
                    <form method="POST" action="{{ route($plan['cta_action']) }}">
                        @csrf
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                            {{ $plan['cta_label'] }}
                        </button>
                    </form>
                    @else
                    <button disabled
                        class="w-full bg-gray-200 text-gray-500 font-semibold py-3 rounded-xl cursor-not-allowed">
                        {{ $plan['cta_label'] }}
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

</body>

</html>