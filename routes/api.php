<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Backend
|--------------------------------------------------------------------------
|
| API REST/JSON consumate dal front-end pubblico (es. health, elenco corsi).
| API pubbliche senza auth; API riservate con auth:sanctum se necessario.
|
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
    ]);
});
