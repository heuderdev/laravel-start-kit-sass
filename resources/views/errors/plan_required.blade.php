<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade necessário</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow p-10 max-w-md w-full text-center border border-gray-200">

        <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mx-auto mb-6">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800">Recurso exclusivo Pro</h1>
        <p class="text-gray-500 mt-3">
            Este recurso está disponível apenas no plano <span class="font-semibold text-blue-600">Pro</span>.
            Faça upgrade para continuar.
        </p>

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