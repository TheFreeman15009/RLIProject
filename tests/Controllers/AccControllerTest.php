<?php

namespace Tests\Controllers;

use App\User;
use App\Driver;
use App\Season;
use App\Points;
use App\Circuit;
use App\Constructor;
use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testFileEncodingIsNotUtf8()
    {
        $points = factory(Points::class)->create();
        $constructor1 = factory(Constructor::class)->create(['game' => 8]);
        $constructor2 = factory(Constructor::class)->create(['game' => 20]);
        $season = factory(Season::class)->create(['constructors' => $constructor1->id . "," . $constructor2->id]);
        $response = $this
                    ->actingAs($this->user)
                    ->withSession(['userRoles' => ['coordinator' => 1]])
                    ->post(route('acc.parseupload'), [
                        'race' => new UploadedFile(resource_path('testData/utf16-le_acc.json'), 'race', 'application/json', null, true),
                        'quali' => new UploadedFile(resource_path('testData/utf16-le_acc.json'), 'quali', 'application/json', null, true),
                        'mode' => 0,
                        'season' => $season->id,
                        'points' => $points->id,
                        'round' => 1
                    ], [
                        'Content-Type' => 'multipart/form-data'
                    ]);

        $response->assertSessionHasErrors([ 'race', 'quali' ]);
        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function testCircuitNotExists()
    {
        $season = factory(Season::class)->create();
        $points = factory(Points::class)->create();
        $response = $this
                    ->actingAs($this->user)
                    ->withSession(['userRoles' => ['coordinator' => 1]])
                    ->post(route('acc.parseupload'), [
                        'race' => new UploadedFile(resource_path('testData/validrace_acc.json'), 'race', 'application/json', null, true),
                        'quali' => new UploadedFile(resource_path('testData/validquali_acc.json'), 'quali', 'application/json', null, true),
                        'mode' => 0,
                        'season' => $season->id,
                        'points' => $points->id,
                        'round' => 1
                    ], [
                        'Content-Type' => 'multipart/form-data'
                    ]);

        $response->assertSessionHasErrors([ 'trackName' ]);
        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function testInvalidStructure()
    {
        $points = factory(Points::class)->create();
        $constructor1 = factory(Constructor::class)->create(['game' => 8]);
        $constructor2 = factory(Constructor::class)->create(['game' => 20]);
        $season = factory(Season::class)->create(['constructors' => $constructor1->id . "," . $constructor2->id]);
        factory(Circuit::class)->create(['series_id' => $season->series_id, 'game' => 'bhatch']);

        $response = $this
                    ->actingAs($this->user)
                    ->withSession(['userRoles' => ['coordinator' => 1]])
                    ->post(route('acc.parseupload'), [
                        'race' => new UploadedFile(resource_path('testData/invalidrace_acc.json'), 'race', 'application/json', null, true),
                        'quali' => new UploadedFile(resource_path('testData/invalidrace_acc.json'), 'quali', 'application/json', null, true),
                        'mode' => 0,
                        'season' => $season->id,
                        'points' => $points->id,
                        'round' => 1
                    ], [
                        'Content-Type' => 'multipart/form-data'
                    ]);

        $response->assertSessionHasErrors([
            'sessionResult.bestlap',
            'sessionResult.leaderBoardLines.0.currentDriver.shortName',
            'sessionResult.leaderBoardLines.1.car.carId'
        ]);
        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function testParseJson()
    {
        $constructor1 = factory(Constructor::class)->create(['game' => 8]);
        $constructor2 = factory(Constructor::class)->create(['game' => 20]);

        $points = factory(Points::class)->create();
        $season = factory(Season::class)->create(['constructors' => $constructor1->id . "," . $constructor2->id]);
        $circuit = factory(Circuit::class)->create(['series_id' => $season->series_id, 'game' => 'bhatch']);

        $user1 = factory(User::class)->create(['steam_id' => '1111']);
        $driver1 = factory(Driver::class)->create(['user_id' => $user1->id]);

        $user2 = factory(User::class)->create(['steam_id' => '222']);
        $driver2 = factory(Driver::class)->create(['user_id' => $user2->id]);

        $response = $this
                    ->actingAs($this->user)
                    ->withSession(['userRoles' => ['coordinator' => 1]])
                    ->post(route('acc.parseupload'), [
                        'race' => new UploadedFile(resource_path('testData/validrace_acc.json'), 'race', 'application/json', null, true),
                        'quali' => new UploadedFile(resource_path('testData/validquali_acc.json'), 'quali', 'application/json', null, true),
                        'mode' => 0,
                        'season' => $season->id,
                        'points' => $points->id,
                        'round' => 1
                    ], [
                        'Content-Type' => 'multipart/form-data'
                    ]);

        $response->assertOk();
        $response->assertSessionHasNoErrors();
        $response->assertExactJson([
            'track' => [
                'circuit_id' => $circuit->id,
                'official' => $circuit->official,
                'display' => $circuit->name,
                "season_id" => $season->id,
                "distance" => 2.8,
                "points" => $points->id,
                "round" => 1
            ],
            'results' => [
                [
                    "position" => 1,
                    "driver" => $driver1->name,
                    "driver_id" => $driver1->id,
                    "team" => $constructor1->name,
                    "constructor_id" => $constructor1->id,
                    "grid" => 2,
                    "stops" => 28,
                    "status" => 1,
                    "fastestlaptime" => "1:24.885",
                    "time" => "40:37.441"
                ],
                [
                    "position" => 2,
                    "driver" => $driver2->name,
                    "driver_id" => $driver2->id,
                    "team" => $constructor2->name,
                    "constructor_id" => $constructor2->id,
                    "grid" => 1,
                    "stops" => 27,
                    "status" => 0,
                    "fastestlaptime" => "1:25.575",
                    "time" => "40:54.886"
                ]
            ]
        ]);
    }
}
