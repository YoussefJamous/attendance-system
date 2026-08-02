<?php

namespace Tests\Feature;

use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    public function test_validation_errors_use_the_standard_api_response_format(): void
    {
        Route::post('/api/test-validation', function (Request $request) {
            $request->validate([
                'name' => ['required'],
            ]);
        });

        $response = $this->postJson('/api/test-validation');

        $response
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'The name field is required.',
                'errors' => [
                    'name' => ['The name field is required.'],
                ],
            ]);
    }

    public function test_authentication_errors_use_the_standard_api_response_format(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authorization_errors_use_the_standard_api_response_format(): void
    {
        Route::get('/api/test-authorization', function () {
            throw new AuthorizationException;
        });

        $response = $this->getJson('/api/test-authorization');

        $response
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'This action is unauthorized.',
            ]);
    }

    public function test_model_not_found_errors_use_the_standard_api_response_format(): void
    {
        Route::get('/api/test-model-not-found', function () {
            throw (new ModelNotFoundException)->setModel(User::class, ['missing-id']);
        });

        $response = $this->getJson('/api/test-model-not-found');

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
            ]);
    }

    public function test_generic_server_errors_use_the_standard_api_response_format(): void
    {
        Route::get('/api/test-server-error', function () {
            throw new Exception('Sensitive internal detail.');
        });

        $response = $this->getJson('/api/test-server-error');

        $response
            ->assertInternalServerError()
            ->assertJson([
                'success' => false,
                'message' => 'Something went wrong.',
            ]);
    }

    public function test_missing_api_routes_use_the_standard_api_response_format(): void
    {
        $response = $this->getJson('/api/missing-route');

        $response
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Not Found',
            ]);
    }
}
