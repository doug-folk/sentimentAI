<?php

    namespace App\Services;

    use Illuminate\Support\Facades\Http;
    use GuzzleHttp\Client;

    
    class HuggingFaceService
    {
        protected $apiKey;
    
        public function __construct()
        {
            //carrega a chave da api
            $this->apiKey = env('HUGGING_FACE_API_KEY');
        }
    
        public function analyzeSentiment($text)
        {
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post('https://api-inference.huggingface.co/models/distilbert-base-uncased-finetuned-sst-2-english', [
                'inputs' => $text,
            ]);
    
            return $response->json();
        }
    }