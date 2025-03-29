<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postagem;
use App\Services\HuggingFaceService;

class PostagemController extends Controller
{
    public function index()
    {
        $postagens = Postagem::all();
        return view('postagens.index', compact('postagens'));
    }

    public function create()
    {
        return view('postagens.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'texto' => 'required|string|max:500',
            'rede_social' => 'required|string|max:50'
        ]);

        try {
            $huggingFace = new HuggingFaceService();
            $sentiment = $huggingFace->analyzeSentiment($validated['texto']);

            //  dd($sentiment);


            $label = $this->parseSentiment($sentiment);
        } catch (\Exception $e) {
            return redirect()->route('postagens.create')
                ->with('error', 'Erro ao analisar sentimento. Tente novamente mais tarde.');
        }

        Postagem::create([
            'texto' => $validated['texto'],
            'rede_social' => $validated['rede_social'],
            'sentimento' => $label,
        ]);

        return redirect()->route('postagens.index')
            ->with('success', 'Postagem analisada com sucesso!');
    }

    protected function parseSentiment($response)
    {
        if (!is_array($response) || empty($response)) {
            return 'NEUTRAL';
        }

        // Encontra o sentimento com maior score
        $sentiment = collect($response)->sortByDesc('score')->first();

        if (!$sentiment || !isset($sentiment['label'])) {
            return 'NEUTRAL';
        }

        // Mapeia os rótulos para português
        $labelsMap = [
            'POSITIVE' => 'POSITIVO',
            'NEGATIVE' => 'NEGATIVO'
        ];

        return $labelsMap[$sentiment['label']] ?? $sentiment['label'];
    }

    // ... (métodos show, edit, update, destroy permanecem iguais)
}
