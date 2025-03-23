<!DOCTYPE html>
<html>
<head>
    <title>Postagens</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Postagens</h1>
        <a href="{{ route('postagens.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Nova Postagem</a>
        <div class="mt-4">
            @foreach ($postagens as $postagem)
                <div class="bg-white p-4 rounded shadow mb-4">
                    <p class="text-gray-800">{{ $postagem->texto }}</p>
                    <p class="text-sm text-gray-600">Sentimento: {{ $postagem->sentimento }}</p>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>