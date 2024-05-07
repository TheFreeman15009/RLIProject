<?php

namespace Tests\Controllers;

use Tests\TestCase;
use App\User;
use App\Race;
use App\Result;
use App\Driver;
use App\Points;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DriverControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSaveAllotment()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)
                         ->withSession(['userRoles' => [
                            'coordinator' => 1,
                         ]])
                         ->post(route('driver.allot'), [
                            'user_id' => $user->id,
                            'tier' => 3
                         ]);

        $response->assertRedirect();

        $dbUser = Driver::where('user_id', $user->id)->get()->toArray();
        $this->assertCount(1, $dbUser);
        $this->assertEquals($user->name, $dbUser[0]['name']);
        $this->assertEquals(3, $dbUser[0]['tier']);
    }

    private function apiDataSetup(array &$pushedResults)
    {
        $points = new Points();
        $points->P1 = 5;
        $points->P2 = 2;
        $points->P3 = 1;
        $points->save();

        // Creates multiple series per race
        $race = factory(Race::class)->create(['points' => $points->id]);

        $pushedResults = array();
        $result = factory(Result::class)->create(['race_id' => $race->id, 'position' => 1]);
        $result->points = 5;
        array_push($pushedResults, $result);

        $result = factory(Result::class)->create(['race_id' => $race->id, 'position' => 2]);
        $result->points = 2;
        array_push($pushedResults, $result);

        $result = factory(Result::class)->create(['race_id' => $race->id, 'position' => 3]);
        $result->points = 1;
        array_push($pushedResults, $result);

        return $race;
    }

    public function testDriverDataApi()
    {
        $pushedResults = array();
        $race = $this->apiDataSetup($pushedResults);

        $userToken = $pushedResults[0]->driver->user->api_token;
        $response = $this->get(route('telemetry.drivers'), [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $userToken
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'drivers',
            'constructors',
            'series'
        ]);

        foreach ($pushedResults as $res) {
            // Drivers fragment
            $response->assertJsonFragment([
                    "id" => $res->driver->id,
                    "name" => $res->driver->name,
                    "alias" => $res->driver->alias,
                    "user_id" => $res->driver->user->id,
                    "user" => [
                        "id" => $res->driver->user->id,
                        "avatar" => $res->driver->user->avatar,
                        "discord_id" => $res->driver->user->discord_id,
                        "steam_id" => $res->driver->user->steam_id,
                        "xbox" => $res->driver->user->xbox,
                        "psn" => $res->driver->user->psn
                    ],
                    "season_points" => [
                        $race->season_id => $res->points
                    ]
            ]);

            // Constructors fragment
            $response->assertJsonFragment([
                    "id" => $res->constructor->id,
                    "name" => $res->constructor->name,
                    "official" => $res->constructor->official,
                    "game" => $res->constructor->game,
                    "logo" => $res->constructor->logo,
                    "car" => $res->constructor->car,
                    "season_points" => [
                        $race->season_id => $res->points
                    ]
            ]);
        }

        // Series fragment
        $response->assertJsonFragment([
            "id" => $race->season->associatedSeries->id,
            "name" => $race->season->associatedSeries->name,
            "code" => $race->season->associatedSeries->code,
            "games" => $race->season->associatedSeries->games
        ]);
    }

    public function testSeasonDataApi()
    {
        $pushedResults = array();
        $this->apiDataSetup($pushedResults);

        $userToken = $pushedResults[0]->driver->user->api_token;
        $response = $this->get(route('telemetry.season'), [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $userToken
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            [
                'id',
                'game',
                'season',
                'tier',
                'name',
                'series',
                'constructors' => [
                    [

                        "id",
                        "name",
                        "team" => [
                            "id",
                            "name",
                            "official",
                            "game",
                            "logo",
                            "car",
                            "series",
                            "title",
                        ],
                        "flaps",
                        "points",
                        "penalties",
                    ]
                ],
                'tttracks' => [
                    [

                        "id",
                        "name",
                        "location",
                        "country",
                        "official",
                        "display",
                        "track_length",
                        "laps",
                        "flag",
                        "game",
                        "series",
                        "title",
                    ]
                ],
                'drivers' => [
                    [

                        "id",
                        "name",
                        "team" => [
                            "id",
                            "name",
                            "official",
                            "game",
                            "logo",
                            "car",
                            "series",
                            "title",
                        ],
                        "flaps",
                        "points",
                        "penalties",
                        "user",
                    ]
                ]
            ]
        ]);
    }
}
