<!DOCTYPE html>
<html>
<head>
    <title>Criar Postagem</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Criar Postagem</h1>
        <form action="{{ route('postagens.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700">Texto</label>
                <textarea name="texto" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Rede Social</label>
                <input type="text" name="rede_social" class="w-full p-2 border rounded" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Salvar</button>
        </form>
    </div>
</body>
</html>