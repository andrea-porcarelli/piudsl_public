<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-Api-Token' => config('services.piudsl_api.s2s_token')])
                ->get(config('services.piudsl_api.base_url') . '/products/published');

            $products = $response->successful() ? ($response->json('data') ?? []) : [];
        } catch (\Exception) {
            $products = [];
        }

        return view('welcome', compact('products'));
    }
}
