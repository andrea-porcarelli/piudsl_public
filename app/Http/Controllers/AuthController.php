<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        Log::info('AuthController@login: validazione OK', ['email' => $data['email']]);

        $baseUrl = config('services.piudsl_api.base_url');
        $s2sToken = config('services.piudsl_api.s2s_token');
        Log::info('AuthController@login: chiamata API', [
            'url'           => $baseUrl . '/auth/login',
            's2s_token_set' => ! empty($s2sToken),
        ]);

        $response = Http::timeout(10)
            ->withHeaders(['X-Api-Token' => $s2sToken])
            ->post($baseUrl . '/auth/login', $data);

        Log::info('AuthController@login: risposta API', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->status() === 401) {
            return response()->json(['success' => false, 'message' => 'Credenziali non valide.'], 401);
        }

        if ($response->status() === 403) {
            return response()->json(['success' => false, 'message' => 'Accesso non consentito per questo ruolo.'], 403);
        }

        if ($response->status() === 422) {
            return response()->json(['success' => false, 'message' => 'Dati non validi.'], 422);
        }

        if (! $response->successful()) {
            Log::error('AuthController@login: risposta non successful', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return response()->json(['success' => false, 'message' => 'Errore del server. Riprova più tardi.'], 500);
        }

        $payload = $response->json('data');
        Log::info('AuthController@login: payload ricevuto', ['payload_keys' => array_keys($payload ?? [])]);

        $request->session()->regenerate();
        $request->session()->put('auth_token', $payload['token']);
        $request->session()->put('user_id',    $payload['user']['id']);
        $request->session()->put('user_name',  $payload['user']['name']);
        $request->session()->put('user_role',  $payload['user']['role']);

        $role = $payload['user']['role'];
        Log::info('AuthController@login: sessione salvata', ['role' => $role]);

        if ($role === 'technician') {
            return response()->json([
                'success'  => true,
                'role'     => 'technician',
                'redirect' => '/technician',
            ]);
        }

        return response()->json(['success' => true, 'role' => 'user']);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->session()->get('auth_token');

        if ($token) {
            Http::timeout(10)
                ->withHeaders([
                    'X-Api-Token'   => config('services.piudsl_api.s2s_token'),
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->post(config('services.piudsl_api.base_url') . '/auth/logout');
        }

        $request->session()->flush();

        return response()->json(['success' => true]);
    }
}
