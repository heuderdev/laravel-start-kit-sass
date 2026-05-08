{{-- resources/views/errors/tenant-inactive.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Indisponível</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">

        <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-6">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Acesso Indisponível</h1>

        <p class="text-gray-500 mb-2">
            A empresa à qual você pertence não possui um plano ativo para acessar este recurso.
        </p>

        <p class="text-gray-400 text-sm mb-8">
            Entre em contato com o administrador da sua organização para regularizar a situação e liberar o acesso.
        </p>

        @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm rounded-lg px-4 py-3 mb-6">
            {{ session('warning') }}
        </div>
        @endif

        <a href="{{ route('dashboard') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-xl transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar ao Dashboard
        </a>

    </div>

</body>

</html>