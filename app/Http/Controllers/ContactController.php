<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    private const REQUEST_TYPES = [
        'info'     => 'Informazioni generali',
        'pricing'  => 'Tariffe e prezzi',
        'business' => 'Soluzioni business',
        'support'  => 'Supporto tecnico',
        'other'    => 'Altro',
    ];

    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nome'         => 'required|string|max:120',
            'cognome'      => 'required|string|max:120',
            'email'        => 'required|email:rfc|max:190',
            'telefono'     => 'nullable|string|max:40',
            'tipo'         => 'required|string|in:' . implode(',', array_keys(self::REQUEST_TYPES)),
            'messaggio'    => 'required|string|max:5000',
            'website'      => 'nullable|string|max:0', // honeypot
        ]);

        // Honeypot: se compilato è un bot — rispondiamo OK ma non spediamo
        if (! empty($request->input('website'))) {
            return response()->json(['ok' => true]);
        }

        $tipoLabel = self::REQUEST_TYPES[$data['tipo']] ?? $data['tipo'];

        $body = "Nuova richiesta dal form di contatto del sito.\n\n"
            . "Nome:        {$data['nome']}\n"
            . "Cognome:     {$data['cognome']}\n"
            . "Email:       {$data['email']}\n"
            . 'Telefono:    ' . ($data['telefono'] ?? '—') . "\n"
            . "Tipo:        {$tipoLabel}\n\n"
            . "Messaggio:\n"
            . "----------\n"
            . $data['messaggio'] . "\n";

        $recipient = config('services.contact_form.recipient');
        $fullName  = trim($data['nome'] . ' ' . $data['cognome']);

        try {
            Mail::raw($body, function ($message) use ($recipient, $data, $fullName, $tipoLabel) {
                $message->to($recipient)
                    ->replyTo($data['email'], $fullName)
                    ->subject("Richiesta sito ({$tipoLabel}) — {$fullName}");
            });
        } catch (\Throwable $e) {
            Log::error('Invio mail form contatti fallito', [
                'error' => $e->getMessage(),
                'email' => $data['email'],
            ]);

            return response()->json([
                'message' => 'Non siamo riusciti a inviare la richiesta. Riprova tra qualche minuto.',
            ], 500);
        }

        return response()->json(['ok' => true]);
    }
}
