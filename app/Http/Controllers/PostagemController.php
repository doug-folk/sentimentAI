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
        $hugginFace = New HuggingFaceService();
        $sentiment = $hugginFace->analyzeSentiment($request->texto);
// Verifique o que exatamente está sendo enviado e o que está sendo retornado
// dd($sentiment, $request->texto);

        if ($sentiment && isset($sentiment[0])) {
            // Pegue o primeiro sentimento retornado pela API
            $label = $sentiment[0][0]['label'];
        } else {
            // Fallback em caso de resposta inválida
            $label = 'Sentimento não identificado';
        }

        //Cria a postagem com o setimento identificado
        Postagem::create([
            'texto' => $request->texto,
            'rede_social' => $request->rede_social,
            'sentimento' => $label,
        ]);

            return redirect()->route('postagens.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
