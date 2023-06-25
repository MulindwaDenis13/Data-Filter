<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DataFilterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'cash-book'], function () {
    Route::post('import', [DataFilterController::class, 'import_cash_book'])->name('cashbook.import');
});

Route::group(['prefix' => 'bank-statement'], function () {
    Route::post('import', [DataFilterController::class, 'import_bank_statement'])->name('bank_statement.import');
});

Route::get('export/{value}', [DataFilterController::class, 'export_data'])->name('export');