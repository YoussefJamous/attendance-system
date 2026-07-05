<?php

use App\Http\Controllers\API\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




// Test the ApiController
Route::prefix('v1')->group(function () {
    Route::get('/test', function () {
        return (new class extends ApiController {
            public function __invoke()
            {
                return $this->error(
                    message: 'API is working.',
                );
            }
        })();
    });
});