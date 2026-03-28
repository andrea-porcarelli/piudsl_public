<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class TechnicianController extends Controller
{
    private function apiHeaders(Request $request): array
    {
        return [
            'X-Api-Token'   => config('services.piudsl_api.s2s_token'),
            'Authorization' => 'Bearer ' . $request->session()->get('auth_token'),
        ];
    }

    private function baseUrl(): string
    {
        return config('services.piudsl_api.base_url');
    }

    public function dashboard(Request $request): View
    {
        return view('technician.dashboard', [
            'userName'   => $request->session()->get('user_name'),
            'mapsApiKey' => config('services.google.maps_api_key', ''),
        ]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . '/calendar-events');

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function cartActivities(Request $request): JsonResponse
    {
        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . '/cart-activities');

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function tickets(Request $request): JsonResponse
    {
        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . '/tickets');

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function paperInvoices(Request $request): JsonResponse
    {
        $month           = (int) $request->query('month', (int) now()->format('n'));
        $year            = (int) $request->query('year',  (int) now()->format('Y'));
        $lat             = $request->query('lat');
        $lng             = $request->query('lng');
        $includeDelivered = $request->boolean('include_delivered');

        $query = ['month' => $month, 'year' => $year];
        if ($includeDelivered) {
            $query['include_delivered'] = 1;
        }

        $response = Http::timeout(10)
            ->withHeaders(['X-Api-Token' => config('services.piudsl_api.s2s_token')])
            ->get($this->baseUrl() . '/invoices/paper', $query);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        if (! $response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        $payload = $response->json();
        $data    = $payload['data'] ?? [];

        if ($lat !== null && $lng !== null && count($data) > 0) {
            $data = $this->nearestNeighborSort($data, (float) $lat, (float) $lng);
        }

        $payload['data'] = $data;

        return response()->json($payload);
    }

    public function deliverPaperInvoice(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->post($this->baseUrl() . "/invoices/paper/{$id}/deliver", $data);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    private function nearestNeighborSort(array $invoices, float $fromLat, float $fromLng): array
    {
        $remaining = $invoices;
        $sorted    = [];

        while (! empty($remaining)) {
            $bestIdx  = null;
            $bestDist = PHP_FLOAT_MAX;

            foreach ($remaining as $idx => $inv) {
                if (empty($inv['coordinates'])) {
                    continue;
                }
                [$lat, $lng] = array_map('floatval', explode(',', $inv['coordinates'], 2));
                $dist = $this->haversine($fromLat, $fromLng, $lat, $lng);
                if ($dist < $bestDist) {
                    $bestDist = $dist;
                    $bestIdx  = $idx;
                }
            }

            if ($bestIdx === null) {
                // Fatture senza coordinate: le accodiamo in fondo così come sono
                $sorted = array_merge($sorted, array_values($remaining));
                break;
            }

            $inv = $remaining[$bestIdx];
            [$fromLat, $fromLng] = array_map('floatval', explode(',', $inv['coordinates'], 2));
            $inv['distance_km'] = round($bestDist, 2);
            $sorted[] = $inv;
            unset($remaining[$bestIdx]);
        }

        return $sorted;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $R * 2.0 * asin(sqrt($a));
    }

    public function updateTicket(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'ticket_status' => ['sometimes', 'in:open,pending,close'],
            'ticket_level'  => ['sometimes', 'in:normal,low,high'],
            'department'    => ['sometimes', 'in:admins,technicians'],
        ]);

        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->put($this->baseUrl() . "/tickets/{$id}", $data);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }
}
