<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NoticeController extends Controller
{
    /**
     * GET /api/notice
     * Proxy verso l'API esterna. Restituisce l'avviso attivo o null.
     */
    public function show(): JsonResponse
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-Api-Token' => config('services.piudsl_api.s2s_token')])
                ->get(config('services.piudsl_api.base_url') . '/notices/active');

            Log::info('NoticeController: risposta API', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            if ($response->successful()) {
                return response()->json(['notice' => $response->json('data')]);
            }

            Log::warning('NoticeController: risposta non successful', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('NoticeController: eccezione durante la chiamata API', [
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json(['notice' => null]);
    }
}
