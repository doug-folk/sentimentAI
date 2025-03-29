<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class HuggingFaceService
{
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = env('HUGGING_FACE_API_KEY');
        $this->model = 'cardiffnlp/twitter-xlm-roberta-base-sentiment';
    }

    public function analyzeSentiment($text)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post("https://api-inference.huggingface.co/models/{$this->model}", [
                'inputs' => $text,
                'options' => ['wait_for_model' => true]
            ]);
            // dd(env('HUGGING_FACE_API_KEY'));


            if ($response->failed()) {
                throw new \Exception("API Error: " . $response->status());
            }

            $result = $response->json();

            if (!is_array($result) || empty($result[0])) {
                throw new \Exception("Resposta inválida da API");
            }

            return $result[0]; // Retorna toda a análise para ser processada no controller

        } catch (\Exception $e) {
            Log::error('Erro na análise de sentimento: ' . $e->getMessage());
            return null;
        }
    }
}
