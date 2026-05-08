<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura Confirmada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow p-10 max-w-md w-full text-center border border-gray-200">

        <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mx-auto mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800">Assinatura confirmada!</h1>
        <p class="text-gray-500 mt-3">
            Seu plano <span class="font-semibold text-blue-600">Pro</span> está ativo.
            Aproveite todos os recursos disponíveis.
        </p>

        @if(session('success'))
        <p class="mt-4 text-sm text-green-600 bg-green-50 px-4 py-2 rounded-lg">
            {{ session('success') }}
        </p>
        @endif

        <a href="{{ route('dashboard') }}"
            class="mt-8 inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
            Ir para o Dashboard
        </a>

    </div>

</body>

</html>