<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postagem;
use App\Services\HuggingFaceService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Importe Carbon
use Illuminate\Support\Facades\DB; // Importe DB para UPPER

class PostagemController extends Controller
{
    public function index(Request $request)
    {
        $query = Postagem::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('rede_social')) {
            $query->where('rede_social', $request->rede_social);
        }

        // Usando TRIM(UPPER) aqui também para consistência
        if ($request->has('sentimento')) {
            $query->where(DB::raw('TRIM(UPPER(sentimento))'), strtoupper($request->sentimento));
        }

        // Incluindo filtro de palavra-chave para o modal de postagens
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where('texto', 'ILIKE', '%' . $keyword . '%'); // Para PostgreSQL, case-insensitive
            // Para MySQL, você pode usar 'LIKE' ou configurar a collation para case-insensitive
            // $query->where('texto', 'LIKE', '%' . $keyword . '%');
        }

        $postagens = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($postagens);
        }

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

        $label = 'NEUTRAL';

        try {
            $huggingFace = new HuggingFaceService();
            $sentimentAnalysisResult = $huggingFace->analyzeSentiment($validated['texto']);

            Log::info('PostagemController@store: Resposta bruta do HuggingFaceService', ['result' => $sentimentAnalysisResult]);

            if ($sentimentAnalysisResult !== null && is_array($sentimentAnalysisResult) && !empty($sentimentAnalysisResult)) {
                $label = $this->parseSentiment($sentimentAnalysisResult);
            } else {
                Log::warning('PostagemController@store: HuggingFaceService retornou nulo ou vazio para o texto: ' . $validated['texto']);
            }

        } catch (\Exception $e) {
            Log::error('PostagemController@store: Erro ao analisar sentimento com HuggingFaceService: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Erro ao analisar sentimento. Tente novamente mais tarde.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->route('postagens.create')
                ->with('error', 'Erro ao analisar sentimento. Tente novamente mais tarde.');
        }

        $postagem = Postagem::create([
            'texto' => $validated['texto'],
            'rede_social' => $validated['rede_social'],
            'sentimento' => $label,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Postagem analisada com sucesso!',
                'postagem' => $postagem
            ], 201);
        }

        return redirect()->route('postagens.index')
            ->with('success', 'Postagem analisada com sucesso!');
    }

    public function show(Postagem $postagem)
    {
        return response()->json($postagem);
    }

    public function update(Request $request, Postagem $postagem)
    {
        $validated = $request->validate([
            'texto' => 'sometimes|required|string|max:500',
            'rede_social' => 'sometimes|required|string|max:50'
        ]);

        if (isset($validated['texto']) && $validated['texto'] !== $postagem->texto) {
            $label = 'NEUTRAL';
            try {
                $huggingFace = new HuggingFaceService();
                $sentimentAnalysisResult = $huggingFace->analyzeSentiment($validated['texto']);

                Log::info('PostagemController@update: Resposta bruta do HuggingFaceService', ['result' => $sentimentAnalysisResult]);

                if ($sentimentAnalysisResult !== null && is_array($sentimentAnalysisResult) && !empty($sentimentAnalysisResult)) {
                     $label = $this->parseSentiment($sentimentAnalysisResult);
                } else {
                    Log::warning('PostagemController@update: HuggingFaceService retornou nulo ou vazio para o texto: ' . $validated['texto']);
                }
                $validated['sentimento'] = $label;

            } catch (\Exception $e) {
                Log::error('PostagemController@update: Erro ao analisar sentimento com HuggingFaceService: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Erro ao analisar sentimento. Tente novamente mais tarde.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $postagem->update($validated);

        return response()->json([
            'message' => 'Postagem atualizada com sucesso!',
            'postagem' => $postagem
        ]);
    }

    public function destroy(Postagem $postagem)
    {
        $postagem->delete();

        return response()->json([
            'message' => 'Postagem excluída com sucesso!'
        ]);
    }

    protected function parseSentiment($response)
    {
        if (!is_array($response) || empty($response)) {
            Log::warning('parseSentiment: Resposta inválida ou vazia recebida, retornando NEUTRAL.');
            return 'NEUTRAL';
        }

        $sentiment = collect($response)->sortByDesc('score')->first();

        if (!$sentiment || !isset($sentiment['label'])) {
            Log::warning('parseSentiment: Sentimento ou label não encontrado na resposta, retornando NEUTRAL.', ['response' => $response]);
            return 'NEUTRAL';
        }

        $labelsMap = [
            'POSITIVE' => 'POSITIVO',
            'NEGATIVE' => 'NEGATIVO',
            'NEUTRAL' => 'NEUTRAL',
            'positive' => 'POSITIVO',
            'negative' => 'NEGATIVO',
            'neutral' => 'NEUTRAL'
        ];

        // Retorna o label mapeado ou o label original em MAIÚSCULAS se não houver mapeamento específico
        return strtoupper($labelsMap[strtoupper($sentiment['label'])] ?? strtoupper($sentiment['label']));
    }
}
