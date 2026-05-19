<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

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
        // Honeypot anti-bot: se compilato, fingiamo OK e non spediamo nulla.
        if (filled($request->input('website'))) {
            Log::info('ContactController@submit: honeypot triggered', ['ip' => $request->ip()]);
            return response()->json(['ok' => true]);
        }

        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:120'],
            'cognome'   => ['required', 'string', 'max:120'],
            'email'     => ['required', 'string', 'email', 'max:190'],
            'telefono'  => ['nullable', 'string', 'max:40', 'regex:/^[\d\s+().\/-]{5,40}$/'],
            'tipo'      => ['required', Rule::in(array_keys(self::REQUEST_TYPES))],
            'messaggio' => ['required', 'string', 'min:10', 'max:5000'],
        ], $this->messages(), $this->attributes());

        $tipoLabel = self::REQUEST_TYPES[$data['tipo']];
        $fullName  = trim($data['nome'] . ' ' . $data['cognome']);
        $recipient = config('services.contact_form.recipient');

        $body = "Nuova richiesta dal form di contatto del sito.\n\n"
            . "Nome:        {$data['nome']}\n"
            . "Cognome:     {$data['cognome']}\n"
            . "Email:       {$data['email']}\n"
            . 'Telefono:    ' . ($data['telefono'] ?? '—') . "\n"
            . "Tipo:        {$tipoLabel}\n\n"
            . "Messaggio:\n"
            . "----------\n"
            . $data['messaggio'] . "\n";

        try {
            Mail::raw($body, function ($message) use ($recipient, $data, $fullName, $tipoLabel) {
                $message->to($recipient)
                    ->replyTo($data['email'], $fullName)
                    ->subject("Richiesta sito ({$tipoLabel}) — {$fullName}");
            });
        } catch (\Throwable $e) {
            Log::error('ContactController@submit: invio mail fallito', [
                'error' => $e->getMessage(),
                'email' => $data['email'],
            ]);

            return response()->json([
                'message' => 'Non siamo riusciti a inviare la richiesta. Riprova tra qualche minuto.',
            ], 500);
        }

        Log::info('ContactController@submit: richiesta inoltrata', [
            'recipient' => $recipient,
            'email'     => $data['email'],
            'tipo'      => $data['tipo'],
        ]);

        return response()->json(['ok' => true]);
    }

    /** Messaggi di validazione in italiano. */
    private function messages(): array
    {
        return [
            'required'        => 'Il campo :attribute è obbligatorio.',
            'string'          => 'Il campo :attribute non è valido.',
            'max.string'      => 'Il campo :attribute non può superare i :max caratteri.',
            'min.string'      => 'Il campo :attribute deve contenere almeno :min caratteri.',
            'email.email'     => 'Inserisci un indirizzo email valido.',
            'telefono.regex'  => 'Il numero di telefono non è valido (usa cifre, spazi e i simboli + - ( ) / ).',
            'tipo.in'         => 'Seleziona un tipo di richiesta valido.',
        ];
    }

    /** Nomi leggibili dei campi per i messaggi di errore. */
    private function attributes(): array
    {
        return [
            'nome'      => 'nome',
            'cognome'   => 'cognome',
            'email'     => 'email',
            'telefono'  => 'telefono',
            'tipo'      => 'tipo di richiesta',
            'messaggio' => 'messaggio',
        ];
    }
}
