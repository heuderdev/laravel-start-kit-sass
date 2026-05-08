<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Cancelado</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow p-10 max-w-md w-full text-center border border-gray-200">

        <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mx-auto mb-6">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800">Pagamento cancelado</h1>
        <p class="text-gray-500 mt-3">
            Você cancelou o processo de pagamento. Seu plano atual permanece inalterado.
        </p>

        @if(session('warning'))
        <p class="mt-4 text-sm text-yellow-700 bg-yellow-50 px-4 py-2 rounded-lg">
            {{ session('warning') }}
        </p>
        @endif

        <div class="mt-8 flex flex-col gap-3">
            <a href="{{ route('pricing.index') }}"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                Ver planos
            </a>
            <a href="{{ route('dashboard') }}"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-xl transition">
                Voltar ao Dashboard
            </a>
        </div>

    </div>

</body>

</html>