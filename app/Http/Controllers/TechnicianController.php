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
            'userId'     => $request->session()->get('user_id'),
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
        $query = [];
        if ($request->filled('date')) {
            $request->validate(['date' => 'date_format:Y-m-d']);
            $query['date'] = $request->input('date');
        }

        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . '/cart-activities', $query);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function tickets(Request $request): JsonResponse
    {
        return $this->proxy($request, 'get', '/tickets');
    }

    public function updateTicket(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'ticket_status' => ['sometimes', 'string', 'in:open,pending,close'],
            'ticket_level'  => ['sometimes', 'string', 'in:normal,low,high'],
            'department'    => ['sometimes', 'nullable', 'string'],
        ]);

        return $this->proxy($request, 'put', "/tickets/{$id}", $data);
    }

    public function addTicketNote(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        return $this->proxy($request, 'post', "/tickets/{$id}/notes", $data);
    }

    public function uploadTicketAttachment(Request $request, int $id): JsonResponse
    {
        $request->validate(['images' => ['required'], 'images.*' => ['file', 'image', 'max:10240']]);

        return $this->proxyUpload($request, "/tickets/{$id}/attachments");
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
        $data    = $this->normalizePaperInvoices($payload['data'] ?? []);

        if ($lat !== null && $lng !== null && count($data) > 0) {
            $data = $this->nearestNeighborSort($data, (float) $lat, (float) $lng);
        }

        $payload['data'] = $data;

        return response()->json($payload);
    }

    /** @param  array<int, array<string, mixed>>  $data */
    private function normalizePaperInvoices(array $data): array
    {
        return array_map(fn (array $inv) => $this->normalizePaperInvoice($inv), $data);
    }

    /** @param  array<string, mixed>  $inv */
    private function normalizePaperInvoice(array $inv): array
    {
        $inv['phone'] = $inv['phone']
            ?? $inv['customer_phone']
            ?? $inv['mobile']
            ?? $inv['telefono']
            ?? null;

        $inv['delivery_address'] = $this->cleanAddress(
            $inv['delivery_address'] ?? $inv['invoice_delivery_address'] ?? null
        );
        $inv['installation_address'] = $this->cleanAddress(
            $inv['installation_address'] ?? $inv['install_address'] ?? null
        );
        $inv['customer_address'] = $this->cleanAddress(
            $inv['customer_address']
            ?? $inv['billing_address']
            ?? $inv['registry_address']
            ?? $inv['anagrafica_address']
            ?? $inv['user_address']
            ?? null
        );

        $resolved = $this->resolveInvoiceDisplayAddress($inv);
        $inv['display_address']       = $resolved['address'] ?? null;
        $inv['display_address_label'] = $resolved['label'] ?? null;

        $inv['is_blc'] = (bool) (
            $inv['is_blc']
            ?? (($inv['invoice_type'] ?? null) === 'blc')
        );

        return $inv;
    }

    /** @param  array<string, mixed>  $inv
     * @return array{label: string, address: string, source: string}|null
     */
    private function resolveInvoiceDisplayAddress(array $inv): ?array
    {
        if ($inv['delivery_address']) {
            return [
                'label'   => 'Indirizzo consegna',
                'address' => $inv['delivery_address'],
                'source'  => 'delivery',
            ];
        }

        if ($inv['installation_address']) {
            return [
                'label'   => 'Indirizzo installazione',
                'address' => $inv['installation_address'],
                'source'  => 'installation',
            ];
        }

        if ($inv['customer_address']) {
            return [
                'label'   => 'Indirizzo cliente',
                'address' => $inv['customer_address'],
                'source'  => 'customer',
            ];
        }

        $full = $this->cleanAddress($inv['full_address'] ?? null);
        if ($full) {
            return [
                'label'   => 'Indirizzo',
                'address' => $full,
                'source'  => 'full',
            ];
        }

        return null;
    }

    private function cleanAddress(?string $address): ?string
    {
        if ($address === null) {
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/', ' ', $address) ?? '');

        if ($trimmed === '' || $trimmed === ',' || preg_match('/^[\s,]+$/', $trimmed)) {
            return null;
        }

        return $trimmed;
    }

    public function technicianCashSummary(Request $request): JsonResponse
    {
        $query = [];
        if ($request->filled('date')) {
            $query['date'] = $request->query('date');
        }

        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . '/technician-cash/summary', $query);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function deliverPaperInvoice(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'notes'        => ['sometimes', 'nullable', 'string', 'max:1000'],
            'paid_in_cash' => ['sometimes', 'boolean'],
            'payment_note' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->post($this->baseUrl() . "/invoices/paper/{$id}/deliver", $data);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function undeliverPaperInvoice(Request $request, int $id): JsonResponse
    {
        $response = Http::timeout(10)
            ->withHeaders($this->apiHeaders($request))
            ->post($this->baseUrl() . "/invoices/paper/{$id}/undeliver");

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        return response()->json($response->json(), $response->status());
    }

    public function equipmentRecoveries(Request $request): JsonResponse
    {
        $lat               = $request->query('lat');
        $lng               = $request->query('lng');
        $includeCompleted  = $request->boolean('include_completed');

        $query = [];
        if ($includeCompleted) {
            $query['include_completed'] = 1;
        }
        if ($lat !== null && $lng !== null) {
            $query['lat'] = $lat;
            $query['lng'] = $lng;
        }

        $response = Http::timeout(15)
            ->withHeaders(['X-Api-Token' => config('services.piudsl_api.s2s_token')])
            ->get($this->baseUrl() . '/equipment-recoveries', $query);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        if (! $response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        $payload = $response->json();
        $data    = $this->normalizeEquipmentRecoveries($payload['data'] ?? []);

        if ($lat !== null && $lng !== null && count($data) > 0) {
            $data = $this->nearestNeighborSort($data, (float) $lat, (float) $lng);
        }

        $payload['data'] = $data;

        return response()->json($payload);
    }

    public function equipmentRecoveryDetail(Request $request, int $id): JsonResponse
    {
        $response = Http::timeout(15)
            ->withHeaders(['X-Api-Token' => config('services.piudsl_api.s2s_token')])
            ->get($this->baseUrl() . "/equipment-recoveries/{$id}");

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Sessione scaduta.'], 401);
        }

        if (! $response->successful()) {
            return response()->json($response->json(), $response->status());
        }

        $payload = $response->json();
        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload['data'] = $this->normalizeEquipmentRecovery($payload['data']);
        }

        return response()->json($payload, $response->status());
    }

    public function equipmentRecoveryContact(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        return $this->proxyEquipmentRecoveryAction($request, $id, 'contact', $data);
    }

    public function equipmentRecoveryComplete(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'note'         => ['sometimes', 'nullable', 'string', 'max:2000'],
            'invoice_paid' => ['sometimes', 'boolean'],
            'invoice_id'   => ['sometimes', 'nullable', 'integer'],
            'payment_note' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        return $this->proxyEquipmentRecoveryAction($request, $id, 'complete', $data);
    }

    /** @param  array<int, array<string, mixed>>  $data */
    private function normalizeEquipmentRecoveries(array $data): array
    {
        return array_map(fn (array $item) => $this->normalizeEquipmentRecovery($item), $data);
    }

    /** @param  array<string, mixed>  $item */
    private function normalizeEquipmentRecovery(array $item): array
    {
        $item['phone'] = $item['phone']
            ?? $item['customer_phone']
            ?? $item['mobile']
            ?? $item['telefono']
            ?? null;

        $item['installation_address'] = $this->cleanAddress($item['installation_address'] ?? null);
        $item['customer_address']     = $this->cleanAddress($item['customer_address'] ?? null);

        $resolved = $this->resolveRecoveryDisplayAddress($item);
        $item['display_address']       = $resolved['address'] ?? null;
        $item['display_address_label'] = $resolved['label'] ?? null;

        $invoiceStatusKnown = array_key_exists('has_unpaid_invoices', $item)
            || array_key_exists('has_unpaid_invoice', $item)
            || array_key_exists('unpaid_invoices_count', $item)
            || array_key_exists('unpaid_invoices', $item)
            || array_key_exists('outstanding_invoices', $item)
            || array_key_exists('unpaid_invoices_summary', $item);

        $unpaid = $item['unpaid_invoices'] ?? $item['outstanding_invoices'] ?? [];
        if (! is_array($unpaid)) {
            $unpaid = [];
        }
        $item['unpaid_invoices'] = $unpaid;

        $count = (int) ($item['unpaid_invoices_count'] ?? count($unpaid));
        $item['unpaid_invoices_count'] = $count;

        $item['has_unpaid_invoices'] = (bool) (
            $item['has_unpaid_invoices']
            ?? $item['has_unpaid_invoice']
            ?? $count > 0
        );

        if ($item['has_unpaid_invoices'] && empty($item['unpaid_invoices_summary'])) {
            $item['unpaid_invoices_summary'] = $this->buildUnpaidInvoicesSummary($item);
        }

        $item['invoice_status_known'] = $invoiceStatusKnown;

        return $item;
    }

    /** @param  array<string, mixed>  $item */
    private function buildUnpaidInvoicesSummary(array $item): string
    {
        if (! empty($item['unpaid_invoices_summary'])) {
            return (string) $item['unpaid_invoices_summary'];
        }

        $parts = [];
        foreach ($item['unpaid_invoices'] as $inv) {
            if (! is_array($inv)) {
                continue;
            }
            $label = $inv['label']
                ?? $inv['description']
                ?? $inv['invoice_name']
                ?? $inv['name']
                ?? null;
            if ($label) {
                $parts[] = $label;
            }
        }

        if ($parts !== []) {
            return implode(' · ', $parts);
        }

        $count = (int) ($item['unpaid_invoices_count'] ?? 0);

        return $count === 1 ? '1 fattura da saldare' : "{$count} fatture da saldare";
    }

    /** @param  array<string, mixed>  $item
     * @return array{label: string, address: string, source: string}|null
     */
    private function resolveRecoveryDisplayAddress(array $item): ?array
    {
        if ($item['installation_address']) {
            return [
                'label'   => 'Indirizzo installazione',
                'address' => $item['installation_address'],
                'source'  => 'installation',
            ];
        }

        if ($item['customer_address']) {
            return [
                'label'   => 'Indirizzo cliente',
                'address' => $item['customer_address'],
                'source'  => 'customer',
            ];
        }

        $full = $this->cleanAddress($item['full_address'] ?? null);
        if ($full) {
            return [
                'label'   => 'Indirizzo',
                'address' => $full,
                'source'  => 'full',
            ];
        }

        return null;
    }

    /** @param  array<string, mixed>  $data */
    private function proxyEquipmentRecoveryAction(Request $request, int $id, string $action, array $data): JsonResponse
    {
        $response = Http::timeout(15)
            ->withHeaders($this->apiHeaders($request))
            ->post($this->baseUrl() . "/equipment-recoveries/{$id}/{$action}", $data);

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

        $payload = $response->json();
        if ($payload === null && $response->body() !== '') {
            return response()->json([
                'message' => 'Errore del gestionale. Riprova più tardi.',
            ], $response->status() ?: 502);
        }

        return response()->json($payload, $response->status());
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

    // ── Cart Activities ────────────────────────────────────────────────────────

    public function cartActivityDetail(Request $request, int $id): JsonResponse
    {
        $response = Http::timeout(15)
            ->withHeaders($this->apiHeaders($request))
            ->get($this->baseUrl() . "/cart-activities/{$id}");

        if ($response->status() === 401) {
            return response()->json(['message' => 'Sessione scaduta.'], 401);
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            if ($response->successful()) {
                return response()->json(['message' => 'Risposta gestionale non valida.'], 502);
            }

            return response()->json([
                'message' => 'Errore del gestionale. Riprova più tardi.',
            ], $response->status() >= 400 ? $response->status() : 502);
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            try {
                $payload['data'] = $this->enrichCartActivityOffer($payload['data']);
            } catch (\Throwable $e) {
                // Non bloccare il dettaglio se il catalogo prezzi non è disponibile.
            }
        }

        return response()->json($payload, $response->status());
    }

    /**
     * Aggiunge public_price (mensile IVA inclusa dal catalogo prodotti) all'offerta.
     * offer.price dal gestionale può essere imponibile o canone bimestrale.
     *
     * @param  array<string, mixed>  $activity
     * @return array<string, mixed>
     */
    private function enrichCartActivityOffer(array $activity): array
    {
        $offer = $activity['offer'] ?? null;
        if (! is_array($offer)) {
            return $activity;
        }

        $publicPrice = $offer['public_price']
            ?? $offer['price_vat']
            ?? $offer['price_with_vat']
            ?? $offer['price_gross']
            ?? null;

        if ($publicPrice === null || $publicPrice === '') {
            $publicPrice = $this->resolvePublishedPublicPrice(
                (string) ($offer['name'] ?? ''),
                isset($offer['product_id']) ? (int) $offer['product_id'] : null
            );
        }

        if ($publicPrice !== null && $publicPrice !== '') {
            $offer['public_price'] = round((float) $publicPrice, 2);
            $offer['price_display'] = $offer['public_price'];
            $offer['price_includes_vat'] = true;
        } else {
            $offer['price_display'] = isset($offer['price']) ? round((float) $offer['price'], 2) : null;
            $offer['price_includes_vat'] = false;
        }

        $activity['offer'] = $offer;

        return $activity;
    }

    private function resolvePublishedPublicPrice(string $offerName, ?int $productId = null): ?float
    {
        $catalog = $this->publishedProductsCatalog();
        if ($productId && isset($catalog['by_id'][$productId])) {
            return $catalog['by_id'][$productId];
        }

        $key = $this->normalizeProductName($offerName);
        if ($key !== '' && isset($catalog['by_name'][$key])) {
            return $catalog['by_name'][$key];
        }

        return null;
    }

    /**
     * Catalogo prezzi pubblici in memoria per-request (niente Cache DB/Redis).
     *
     * @return array{by_id: array<int, float>, by_name: array<string, float>}
     */
    private function publishedProductsCatalog(): array
    {
        static $catalog = null;
        if (is_array($catalog)) {
            return $catalog;
        }

        $byId = [];
        $byName = [];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Api-Token' => config('services.piudsl_api.s2s_token')])
                ->get($this->baseUrl() . '/products/published');

            if ($response->successful()) {
                foreach ($response->json('data') ?? [] as $product) {
                    if (! is_array($product)) {
                        continue;
                    }
                    $price = $product['public_price'] ?? null;
                    if ($price === null || $price === '') {
                        continue;
                    }
                    $price = round((float) $price, 2);
                    if (isset($product['id'])) {
                        $byId[(int) $product['id']] = $price;
                    }
                    foreach (['label', 'offer_title', 'name'] as $field) {
                        $norm = $this->normalizeProductName((string) ($product[$field] ?? ''));
                        if ($norm !== '') {
                            $byName[$norm] = $price;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Catalogo non disponibile: resta fallback su offer.price.
        }

        $catalog = ['by_id' => $byId, 'by_name' => $byName];

        return $catalog;
    }

    private function normalizeProductName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return $name;
    }

    public function updateCartActivity(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'string', 'in:open,suspended,completed'],
            'note'   => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        return $this->proxy($request, 'patch', "/cart-activities/{$id}", $data);
    }

    public function updateCartActivityPlantCoordinates(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'coordinates' => ['required', 'string', 'regex:/^-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?$/'],
        ]);

        return $this->proxy($request, 'post', "/cart-activities/{$id}/plant-coordinates", $data);
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

    public function createReport(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'note' => ['required', 'string', 'max:2000'],
        ]);

        return $this->proxy($request, 'post', '/reports', $data);
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

}
