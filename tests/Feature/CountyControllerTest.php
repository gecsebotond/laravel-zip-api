<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\County;
use App\Models\User;

class CountyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->token = $user->createToken('TestToken')->plainTextToken;
    }

    public function test_returns_all_counties()
    {
        County::factory()->create(['name' => 'Pest']);
        County::factory()->create(['name' => 'Baranya']);

        $response = $this->getJson('/api/counties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    ['id', 'name', 'places']
                ]
            ])
            ->assertJsonFragment(['name' => 'Pest'])
            ->assertJsonFragment(['name' => 'Baranya']);
    }

    public function test_returns_a_single_county()
    {
        $county = County::factory()->create(['name' => 'Fejér']);

        $response = $this->getJson("/api/counties/{$county->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $county->id,
                    'name' => 'Fejér',
                ]
            ]);
    }

    public function test_returns_404_for_nonexistent_county()
    {
        $response = $this->getJson('/api/counties/9999');
        $response->assertStatus(404);
    }

    public function test_creates_a_new_county_when_authenticated()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/counties', [
            'name' => 'Somogy',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'County created successfully',
                'data' => ['name' => 'Somogy']
            ]);

        $this->assertDatabaseHas('counties', ['name' => 'Somogy']);
    }

    public function test_fails_to_create_a_county_without_authentication()
    {
        $response = $this->postJson('/api/counties', [
            'name' => 'Unauthorized County',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('counties', ['name' => 'Unauthorized County']);
    }

    public function test_validates_duplicate_county_names_on_create()
    {
        County::factory()->create(['name' => 'Pest']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/counties', [
            'name' => 'Pest',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_updates_a_county_when_authenticated()
    {
        $county = County::factory()->create(['name' => 'OldName']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/counties/{$county->id}", [
            'name' => 'NewName',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'County updated successfully',
                'data' => ['name' => 'NewName']
            ]);

        $this->assertDatabaseHas('counties', ['id' => $county->id, 'name' => 'NewName']);
    }

    public function test_fails_to_update_county_without_authentication()
    {
        $county = County::factory()->create(['name' => 'Original']);

        $response = $this->putJson("/api/counties/{$county->id}", [
            'name' => 'ShouldNotUpdate',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('counties', ['id' => $county->id, 'name' => 'Original']);
    }

    public function test_deletes_a_county_when_authenticated()
    {
        $county = County::factory()->create(['name' => 'ToDelete']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/counties/{$county->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('counties', ['id' => $county->id]);
    }

    public function test_fails_to_delete_without_authentication()
    {
        $county = County::factory()->create(['name' => 'ToFailDelete']);

        $response = $this->deleteJson("/api/counties/{$county->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('counties', ['id' => $county->id]);
    }

}
