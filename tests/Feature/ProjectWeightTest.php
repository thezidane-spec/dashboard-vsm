<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProjectWeightTest extends TestCase
{
    public function test_procurement_can_save_material_weight(): void
    {
        $project = $this->projectMock();
        $project->shouldReceive('update')->once()->with(['total_material_weight_kg' => '1250.5'])->andReturn(true);

        $response = (new DashboardController())->updateMaterialWeight(
            $this->request(['total_material_weight_kg' => '1250.5'], 'procurement'),
            $project
        );

        $this->assertSame(route('projects.edit', 123) . '#procurement-weight', $response->getTargetUrl());
    }

    public function test_qaqc_can_save_finished_project_weight(): void
    {
        $project = $this->projectMock();
        $project->shouldReceive('update')->once()->with(['finished_project_weight_kg' => '1180'])->andReturn(true);

        $response = (new DashboardController())->updateFinishedWeight(
            $this->request(['finished_project_weight_kg' => '1180'], 'qc_qa'),
            $project
        );

        $this->assertSame(route('projects.edit', 123) . '#finished-weight', $response->getTargetUrl());
    }

    public function test_weight_can_be_deleted(): void
    {
        $project = $this->projectMock();
        $project->shouldReceive('update')->once()->with(['total_material_weight_kg' => null])->andReturn(true);

        $response = (new DashboardController())->deleteMaterialWeight($this->request([], 'procurement'), $project);

        $this->assertSame(route('projects.edit', 123) . '#procurement-weight', $response->getTargetUrl());
    }

    public function test_qaqc_can_delete_finished_project_weight(): void
    {
        $project = $this->projectMock();
        $project->shouldReceive('update')->once()->with(['finished_project_weight_kg' => null])->andReturn(true);

        $response = (new DashboardController())->deleteFinishedWeight($this->request([], 'qc_qa'), $project);

        $this->assertSame(route('projects.edit', 123) . '#finished-weight', $response->getTargetUrl());
    }

    public function test_other_roles_cannot_change_material_weight(): void
    {
        $this->expectException(HttpException::class);

        (new DashboardController())->updateMaterialWeight(
            $this->request(['total_material_weight_kg' => '100'], 'engineering'),
            $this->projectMock()
        );
    }

    public function test_weight_must_not_be_negative(): void
    {
        $this->expectException(ValidationException::class);

        (new DashboardController())->updateMaterialWeight(
            $this->request(['total_material_weight_kg' => '-1'], 'procurement'),
            $this->projectMock()
        );
    }

    private function projectMock()
    {
        $project = Mockery::mock(Project::class)->makePartial();
        $project->setAttribute('id', 123);

        return $project;
    }

    private function request(array $input, string $role): Request
    {
        $request = Request::create('/', 'PUT', $input);
        $request->setUserResolver(fn () => (object) ['role' => $role]);

        return $request;
    }
}
