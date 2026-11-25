<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class VsmApiAccessTest extends TestCase
{
    public function test_vsm_api_endpoints_are_public(): void
    {
        $paths = [
            '/api/v1/ping',
            '/api/v1/projects',
            '/api/v1/projects/VSM-77',
            '/api/vsm/summary',
            '/api/vsm/projects',
            '/api/vsm/projects/VSM-77',
            '/api/vsm/material',
        ];

        foreach ($paths as $path) {
            $route = app('router')->getRoutes()->match(Request::create($path, 'GET'));

            $this->assertNotContains('vsm.api', $route->gatherMiddleware(), "Expected {$path} to be public.");
        }
    }

    public function test_user_endpoint_remains_authenticated(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/api/user', 'GET'));

        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
    }
}
