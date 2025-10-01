<?php

declare(strict_types=1);

use App\Actions\User\GetAvatarAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

describe('FollowedHuntersController', function () {
    beforeEach(function () {
        Event::fake();
        $this->user = User::factory()->create();
        actingAs($this->user);
    });

    describe('index', function () {
        it('returns empty array when user has no followed hunters', function () {
            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful()
                ->assertJson([])
                ->assertJsonCount(0);
        });

        it('returns followed hunters with correct structure', function () {
            // Create hunters to follow
            $hunter1 = User::factory()->create([
                'name' => 'Alice Hunter',
                'user_name' => 'alice_hunter',
            ]);

            $hunter2 = User::factory()->create([
                'name' => 'Bob Hunter',
                'user_name' => 'bob_hunter',
            ]);

            // Create follow relationships (user follows hunters)
            $this->user->follow($hunter1);
            $this->user->follow($hunter2);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'username',
                        'avatar_url',
                        'level',
                        'is_online',
                    ],
                ])
                ->assertJsonCount(2);

            // Check the response contains the correct hunters
            $responseData = $response->json();
            $hunterIds = collect($responseData)->pluck('id')->toArray();
            expect($hunterIds)->toContain($hunter1->id, $hunter2->id);

            // Verify data structure
            $firstHunter = collect($responseData)->firstWhere('id', $hunter1->id);
            expect($firstHunter)->toHaveKeys(['id', 'name', 'username', 'avatar_url', 'level', 'is_online'])
                ->and($firstHunter['name'])->toBe('Alice Hunter')
                ->and($firstHunter['username'])->toBe('alice_hunter')
                ->and($firstHunter['level'])->toBeNull()
                ->and($firstHunter['is_online'])->toBeFalse();
        });

        it('orders followed hunters by name alphabetically', function () {
            // Create hunters with names in non-alphabetical order
            $hunterZulu = User::factory()->create(['name' => 'Zulu Hunter']);
            $hunterAlpha = User::factory()->create(['name' => 'Alpha Hunter']);
            $hunterBravo = User::factory()->create(['name' => 'Bravo Hunter']);

            // Follow all hunters
            foreach ([$hunterZulu, $hunterAlpha, $hunterBravo] as $hunter) {
                $this->user->follow($hunter);
            }

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful();

            $responseData = $response->json();
            $names = collect($responseData)->pluck('name')->toArray();

            // Should be ordered: Alpha, Bravo, Zulu
            expect($names)->toBe(['Alpha Hunter', 'Bravo Hunter', 'Zulu Hunter']);
        });

        it('only returns accepted follows (not pending)', function () {
            $acceptedHunter = User::factory()->create(['name' => 'Accepted Hunter']);
            $pendingHunter = User::factory()->create(['name' => 'Pending Hunter']);

            // Create one accepted follow and one pending follow
            $this->user->follow($acceptedHunter);

            // Create pending follow by manually inserting into the followables table
            DB::table('followables')->insert([
                'user_id' => $this->user->id,
                'followable_type' => User::class,
                'followable_id' => $pendingHunter->id,
                'accepted_at' => null, // Pending follow
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful()
                ->assertJsonCount(1);

            $responseData = $response->json();
            expect($responseData[0]['name'])->toBe('Accepted Hunter');
        });

        it('includes avatar URL from GetAvatarAction', function () {
            $hunter = User::factory()->create();

            $this->user->follow($hunter);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful();

            $responseData = $response->json();
            $expectedAvatarUrl = (new GetAvatarAction())->handle($hunter);

            expect($responseData[0]['avatar_url'])->toBe($expectedAvatarUrl);
        });

        it('returns only selected fields for performance', function () {
            $hunter = User::factory()->create([
                'name' => 'Test Hunter',
                'user_name' => 'test_hunter',
                'email' => 'test@example.com', // This should not be in response
                'bio' => 'Some bio', // This should not be in response
            ]);

            $this->user->follow($hunter);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful();

            $responseData = $response->json();
            $hunterData = $responseData[0];

            // Should only have the mapped fields, not database fields like email, bio, etc.
            expect($hunterData)->toHaveKeys(['id', 'name', 'username', 'avatar_url', 'level', 'is_online'])
                ->and($hunterData)->not->toHaveKey('email')
                ->and($hunterData)->not->toHaveKey('bio');
        });

        it('handles users with no username gracefully', function () {
            $hunter = User::factory()->create([
                'name' => 'No Username Hunter',
                'user_name' => null,
            ]);

            $this->user->follow($hunter);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful();

            $responseData = $response->json();
            expect($responseData[0]['username'])->toBeNull();
        });

        it('works with large number of followed hunters', function () {
            // Create many hunters to test performance and chunking
            $hunters = User::factory()->count(50)->create();

            foreach ($hunters as $hunter) {
                $this->user->follow($hunter);
            }

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful()
                ->assertJsonCount(50);

            // Verify they're ordered by name
            $responseData = $response->json();
            $names = collect($responseData)->pluck('name')->toArray();
            $sortedNames = collect($names)->sort()->values()->toArray();

            expect($names)->toBe($sortedNames);
        });

        it('does not include self in followed hunters', function () {
            // User cannot follow themselves, but let's ensure they're not included
            $otherHunter = User::factory()->create();

            $this->user->follow($otherHunter);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful()
                ->assertJsonCount(1);

            $responseData = $response->json();
            $hunterIds = collect($responseData)->pluck('id')->toArray();

            expect($hunterIds)->not->toContain($this->user->id)
                ->and($hunterIds)->toContain($otherHunter->id);
        });

        it('sets default values for level and is_online', function () {
            $hunter = User::factory()->create();

            $this->user->follow($hunter);

            $response = $this->getJson('/followed-hunters');

            $response->assertSuccessful();

            $responseData = $response->json();
            expect($responseData[0]['level'])->toBeNull()
                ->and($responseData[0]['is_online'])->toBeFalse();
        });
    });
});

describe('FollowedHuntersController - Unauthorized Access', function () {
    it('requires authentication', function () {
        $response = $this->getJson('/followed-hunters');

        $response->assertUnauthorized();
    });
});
