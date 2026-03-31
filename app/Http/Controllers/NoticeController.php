<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

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

            if ($response->successful()) {
                return response()->json(['notice' => $response->json('data')]);
            }
        } catch (\Exception) {
            // API non raggiungibile: nessun avviso mostrato
        }

        return response()->json(['notice' => null]);
    }
}
