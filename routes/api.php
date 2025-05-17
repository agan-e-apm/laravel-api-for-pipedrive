<?php
use App\Http\Controllers\PipedriveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/pipedrive-customer-data', [PipedriveController::class, 'getCustomerData']);
