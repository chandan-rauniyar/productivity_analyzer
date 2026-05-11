<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Productivity Analyzer' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <section class="w-full max-w-md bg-white rounded-xl shadow p-6">
            <h1 class="text-2xl font-semibold mb-2">{{ $heading ?? 'Productivity Analyzer' }}</h1>
            @isset($subtitle)
                <p class="text-sm text-slate-600 mb-6">{{ $subtitle }}</p>
            @endisset
            {{ $slot }}
        </section>
    </main>
</body>
</html>
