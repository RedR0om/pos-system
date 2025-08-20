<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'app');
// Exclude API prefix from SPA catch-all to avoid accidental overlap
Route::view('/{any}', 'app')->where('any', '^(?!api).*$');
