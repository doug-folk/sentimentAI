<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postagem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        Log::info('DashboardController@getStats: Request Params', $request->all());

        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->has('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        Log::info('DashboardController@getStats: Processed Start Date', ['date' => $startDate->toDateTimeString()]);
        Log::info('DashboardController@getStats: Processed End Date', ['date' => $endDate->toDateTimeString()]);

        $totalPostagens = Postagem::whereBetween('created_at', [$startDate, $endDate])->count();
        Log::info('DashboardController@getStats: Total Postagens found in range', ['count' => $totalPostagens]);

        // Usando TRIM(UPPER) para padronizar sentimentos
        $sentimentCounts = Postagem::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('TRIM(UPPER(sentimento)) as sentimento'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('TRIM(UPPER(sentimento))'))
            ->get()
            ->pluck('total', 'sentimento');

        Log::info('DashboardController@getStats: Sentiment Counts in range', ['counts' => $sentimentCounts->toArray()]);

        $positivos = $sentimentCounts['POSITIVO'] ?? 0;
        $negativos = $sentimentCounts['NEGATIVO'] ?? 0;
        $neutros = $sentimentCounts['NEUTRAL'] ?? 0;

        return response()->json([
            'total_postagens' => $totalPostagens,
            'positivos' => $positivos,
            'negativos' => $negativos,
            'neutros' => $neutros,
            'percentual_positivo' => $totalPostagens > 0 ? round(($positivos / $totalPostagens) * 100, 1) : 0,
            'percentual_negativo' => $totalPostagens > 0 ? round(($negativos / $totalPostagens) * 100, 1) : 0,
            'percentual_neutro' => $totalPostagens > 0 ? round(($neutros / $totalPostagens) * 100, 1) : 0,
        ]);
    }

    public function getTrends(Request $request)
    {
        Log::info('DashboardController@getTrends: Request Params', $request->all());

        $days = $request->get('days', 30);
        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : Carbon::now()->subDays($days)->startOfDay();
        $endDate = $request->has('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        Log::info('DashboardController@getTrends: Processed Start Date', ['date' => $startDate->toDateTimeString()]);
        Log::info('DashboardController@getTrends: Processed End Date', ['date' => $endDate->toDateTimeString()]);

        // Usando TRIM(UPPER) para padronizar sentimentos
        $trends = Postagem::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('TRIM(UPPER(sentimento)) as sentimento'),
                DB::raw('count(*) as total')
            )
            ->groupBy('date', DB::raw('TRIM(UPPER(sentimento))'))
            ->orderBy('date')
            ->get();

        Log::info('DashboardController@getTrends: Raw Trends Data from DB (after UPPER)', ['data' => $trends->toArray()]);

        $formattedTrends = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');
            $formattedTrends[$date] = [
                'date' => $date,
                'POSITIVO' => 0,
                'NEGATIVO' => 0,
                'NEUTRAL' => 0,
            ];
            $currentDate->addDay();
        }

        foreach ($trends as $trend) {
            if (isset($formattedTrends[$trend->date])) {
                $formattedTrends[$trend->date][$trend->sentimento] = $trend->total;
            }
        }

        Log::info('DashboardController@getTrends: Formatted Trends Data for Frontend', ['data' => $formattedTrends]);

        return response()->json(array_values($formattedTrends));
    }

    public function getSentimentDistribution(Request $request)
    {
        Log::info('DashboardController@getSentimentDistribution: Request Params', $request->all());
        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->has('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        Log::info('DashboardController@getSentimentDistribution: Processed Dates', ['start' => $startDate->toDateTimeString(), 'end' => $endDate->toDateTimeString()]);

        // Usando TRIM(UPPER) para padronizar sentimentos
        $distribution = Postagem::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('TRIM(UPPER(sentimento)) as name'), DB::raw('count(*) as value'))
            ->groupBy(DB::raw('TRIM(UPPER(sentimento))'))
            ->get()
            ->keyBy('name') // Indexa a coleção pelo 'name' (sentimento)
            ->toArray();
        
        Log::info('DashboardController@getSentimentDistribution: Distribution Data', ['data' => $distribution]);

        // Garante que todas as categorias de sentimento (POSITIVO, NEGATIVO, NEUTRAL) estejam sempre presentes
        $allSentiments = ['POSITIVO', 'NEGATIVO', 'NEUTRAL'];
        $finalDistribution = [];

        foreach ($allSentiments as $sentimentType) {
            $finalDistribution[] = [
                'name' => $sentimentType,
                'value' => $distribution[$sentimentType]['value'] ?? 0 // Usa o valor do DB ou 0 se não existir
            ];
        }

        return response()->json($finalDistribution);
    }

    public function getSocialMediaStats(Request $request)
    {
        Log::info('DashboardController@getSocialMediaStats: Request Params', $request->all());

        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->has('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        Log::info('DashboardController@getSocialMediaStats: Processed Dates', ['start' => $startDate->toDateTimeString(), 'end' => $endDate->toDateTimeString()]);

        // Usando TRIM(UPPER) para padronizar sentimentos
        $stats = Postagem::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'rede_social',
                DB::raw('TRIM(UPPER(sentimento)) as sentimento'),
                DB::raw('count(*) as total')
            )
            ->groupBy('rede_social', DB::raw('TRIM(UPPER(sentimento))'))
            ->get();

        Log::info('DashboardController@getSocialMediaStats: Raw Stats Data from DB (after UPPER)', ['data' => $stats->toArray()]);

        $formattedStats = [];
        
        foreach ($stats as $stat) {
            if (!isset($formattedStats[$stat->rede_social])) {
                $formattedStats[$stat->rede_social] = [
                    'name' => $stat->rede_social,
                    'POSITIVO' => 0,
                    'NEGATIVO' => 0,
                    'NEUTRAL' => 0,
                ];
            }
            $formattedStats[$stat->rede_social][$stat->sentimento] = $stat->total;
        }

        Log::info('DashboardController@getSocialMediaStats: Formatted Stats Data for Frontend', ['data' => $formattedStats]);

        return response()->json(array_values($formattedStats));
    }
}
