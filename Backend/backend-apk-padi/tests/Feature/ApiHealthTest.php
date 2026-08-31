<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    public function test_health_endpoint_identifies_laravel_backend_gateway(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'service' => 'laravel-backend',
                'gateway' => 'frontend-laravel-ai-service',
            ]);
    }
}
