<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class VsmApiAccessTest extends TestCase
{
    public function test_summary_endpoint_is_public(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/api/vsm/summary', 'GET'));

        $this->assertNotContains('vsm.api', $route->gatherMiddleware());
    }

    public function test_project_endpoint_still_requires_api_token(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/api/vsm/projects', 'GET'));

        $this->assertContains('vsm.api', $route->gatherMiddleware());
    }
}
