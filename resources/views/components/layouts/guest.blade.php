<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Vaultify' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900">
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('login') }}" class="mb-8 block text-center text-2xl font-semibold tracking-tight text-violet-700">Vaultify</a>
            {{ $slot }}
        </div>
    </main>
</body>
</html>
