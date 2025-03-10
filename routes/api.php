<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

Route::post('/whatsapp/webhook', [WhatsAppController::class, 'handle']);
Route::get('/whatsapp/webhook', [WhatsAppController::class, 'verify']);