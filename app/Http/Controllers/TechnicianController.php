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
                $parts = explode(',', $inv['coordinates'], 2);
                if (count($parts) < 2) {
                    continue;
                }
                [$lat, $lng] = array_map('floatval', $parts);
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

    // ── Proxy helpers ──────────────────────────────────────────────────────────

    private function proxy(Request $request, string $method, string $path, array $data = []): JsonResponse
    {
        $http = Http::timeout(15)->withHeaders($this->apiHeaders($request));

        $response = match ($method) {
            'get'    => $http->get($this->baseUrl() . $path, $data ?: []),
            'post'   => $http->post($this->baseUrl() . $path, $data),
            'patch'  => $http->patch($this->baseUrl() . $path, $data),
            'put'    => $http->put($this->baseUrl() . $path, $data),
            'delete' => $http->delete($this->baseUrl() . $path),
            default  => $http->get($this->baseUrl() . $path),
        };

        if ($response->status() === 401) {
            return response()->json(['message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    private function proxyUpload(Request $request, string $path): JsonResponse
    {
        $pending = Http::timeout(60)->withHeaders($this->apiHeaders($request));
        $files   = $request->file('images', []);
        if (! is_array($files)) {
            $files = $files ? [$files] : [];
        }

        foreach ($files as $i => $file) {
            $pending = $pending->attach(
                "images[{$i}]",
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName(),
                ['Content-Type' => $file->getMimeType()]
            );
        }

        $response = $pending->post($this->baseUrl() . $path);

        if ($response->status() === 401) {
            return response()->json(['message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    // ── Calendar Events ────────────────────────────────────────────────────────

    public function calendarEventDetail(Request $request, int $id): JsonResponse
    {
        return $this->proxy($request, 'get', "/calendar-events/{$id}");
    }

    public function updateCalendarEvent(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', 'in:open,in_progress,suspended,completed,close'],
            'note'   => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        return $this->proxy($request, 'patch', "/calendar-events/{$id}", $data);
    }

    public function uploadCalendarAttachment(Request $request, int $id): JsonResponse
    {
        $request->validate(['images' => ['required'], 'images.*' => ['file', 'image', 'max:10240']]);

        return $this->proxyUpload($request, "/calendar-events/{$id}/attachments");
    }

    // ── Tickets ────────────────────────────────────────────────────────────────

    public function ticketDetail(Request $request, int $id): JsonResponse
    {
        return $this->proxy($request, 'get', "/tickets/{$id}");
    }

    public function addTicketNote(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        return $this->proxy($request, 'post', "/tickets/{$id}/notes", $data);
    }

    public function uploadTicketAttachment(Request $request, int $id): JsonResponse
    {
        $request->validate(['images' => ['required'], 'images.*' => ['file', 'image', 'max:10240']]);

        return $this->proxyUpload($request, "/tickets/{$id}/attachments");
    }

    // ── Cart Activities ────────────────────────────────────────────────────────

    public function cartActivityDetail(Request $request, int $id): JsonResponse
    {
        return $this->proxy($request, 'get', "/cart-activities/{$id}");
    }

    public function updateCartActivity(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', 'in:open,suspended,completed'],
            'note'   => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        return $this->proxy($request, 'patch', "/cart-activities/{$id}", $data);
    }

    public function uploadCartActivityAttachment(Request $request, int $id): JsonResponse
    {
        $request->validate(['images' => ['required'], 'images.*' => ['file', 'image', 'max:10240']]);

        return $this->proxyUpload($request, "/cart-activities/{$id}/attachments");
    }

    public function addExtraProduct(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        return $this->proxy($request, 'post', "/cart-activities/{$id}/extra-products", $data);
    }

    public function removeExtraProduct(Request $request, int $id, int $extraProductId): JsonResponse
    {
        return $this->proxy($request, 'delete', "/cart-activities/{$id}/extra-products/{$extraProductId}");
    }

    // ── Products ───────────────────────────────────────────────────────────────

    public function products(Request $request): JsonResponse
    {
        $types = (array) $request->query('types', ['product', 'supplement']);
        $qs    = implode('&', array_map(fn ($t) => 'types[]=' . urlencode($t), $types));

        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . '/products?' . $qs);

        if ($response->status() === 401) {
            return response()->json(['message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    // ── Tickets (update) ───────────────────────────────────────────────────────

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
