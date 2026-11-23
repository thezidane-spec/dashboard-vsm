<?php

namespace Tests\Unit;

use App\Models\Project;
use PHPUnit\Framework\TestCase;

class ProjectPpicTasksTest extends TestCase
{
    public function test_ppic_tasks_include_vendor_and_progress_report(): void
    {
        $this->assertArrayHasKey('task_vendor', Project::$ppicTasks);
        $this->assertArrayHasKey('task_progress_report', Project::$ppicTasks);
        $this->assertEquals(7, array_sum(Project::$ppicTasks));
    }
}