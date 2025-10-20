<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Place;
use App\Models\County;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $county;

    protected function setUp(): void
    {
        parent::setUp();

        // Create authenticated user and token
        $user = User::factory()->create();
        $this->token = $user->createToken('TestToken')->plainTextToken;

        // Create a county for foreign key relation
        $this->county = County::factory()->create(['name' => 'Pest']);
    }

    public function test_returns_all_places()
    {
        Place::factory()->create([
            'postal_code' => '1011',
            'name' => 'Budapest',
            'county_id' => $this->county->id,
        ]);

        Place::factory()->create([
            'postal_code' => '7400',
            'name' => 'Kaposvár',
            'county_id' => $this->county->id,
        ]);

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Budapest'])
            ->assertJsonFragment(['name' => 'Kaposvár'])
            ->assertJsonStructure([
                '*' => ['id', 'postal_code', 'name', 'county']
            ]);
    }

    public function test_returns_a_single_place()
    {
        $place = Place::factory()->create([
            'postal_code' => '2500',
            'name' => 'Esztergom',
            'county_id' => $this->county->id,
        ]);

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $place->id,
                'postal_code' => '2500',
                'name' => 'Esztergom',
                'county' => [
                    'id' => $this->county->id,
                    'name' => 'Pest'
                ]
            ]);
    }

    public function test_returns_404_for_nonexistent_place()
    {
        $response = $this->getJson('/api/places/9999');
        $response->assertStatus(404);
    }

    public function test_creates_a_place_when_authenticated()
    {
        $data = [
            'postal_code' => '6000',
            'name' => 'Kecskemét',
            'county_id' => $this->county->id,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/places', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Kecskemét']);

        $this->assertDatabaseHas('places', $data);
    }

    public function test_fails_to_create_place_without_authentication()
    {
        $data = [
            'postal_code' => '8000',
            'name' => 'Székesfehérvár',
            'county_id' => $this->county->id,
        ];

        $response = $this->postJson('/api/places', $data);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('places', $data);
    }

    public function test_validates_missing_fields_on_create()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/places', [
            'postal_code' => '',
            'name' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['postal_code', 'name', 'county_id']);
    }

    public function test_updates_a_place_when_authenticated()
    {
        $place = Place::factory()->create([
            'postal_code' => '2000',
            'name' => 'OldName',
            'county_id' => $this->county->id,
        ]);

        $data = [
            'postal_code' => '2000',
            'name' => 'NewName',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/places/{$place->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'NewName']);

        $this->assertDatabaseHas('places', array_merge(['id' => $place->id], $data));
    }

    public function test_fails_to_update_place_without_authentication()
    {
        $place = Place::factory()->create([
            'postal_code' => '9000',
            'name' => 'Győr',
            'county_id' => $this->county->id,
        ]);

        $response = $this->putJson("/api/places/{$place->id}", [
            'name' => 'ShouldNotUpdate'
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('places', ['id' => $place->id, 'name' => 'Győr']);
    }

    public function test_deletes_a_place_when_authenticated()
    {
        $place = Place::factory()->create([
            'postal_code' => '3100',
            'name' => 'Salgótarján',
            'county_id' => $this->county->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }

    public function test_fails_to_delete_place_without_authentication()
    {
        $place = Place::factory()->create([
            'postal_code' => '9700',
            'name' => 'Szombathely',
            'county_id' => $this->county->id,
        ]);

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('places', ['id' => $place->id]);
    }
}
