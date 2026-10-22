<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Estudar PHP',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('title', 'Estudar PHP')
            ->assertJsonPath('completed', false);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Estudar PHP',
            'completed' => false,
        ]);
    }

    public function test_cannot_create_a_task_with_only_spaces(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => '     ',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_cannot_create_a_task_with_a_title_longer_than_255_characters(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => str_repeat('a', 256),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_can_update_only_the_completed_status(): void
    {
        $task = Task::create([
            'title' => 'Estudar Laravel',
            'completed' => false,
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'completed' => true,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('title', 'Estudar Laravel')
            ->assertJsonPath('completed', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Estudar Laravel',
            'completed' => true,
        ]);
    }

    public function test_cannot_update_a_task_with_an_empty_title(): void
    {
        $task = Task::create([
            'title' => 'Estudar Laravel',
            'completed' => false,
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => '     ',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Estudar Laravel',
        ]);
    }
}