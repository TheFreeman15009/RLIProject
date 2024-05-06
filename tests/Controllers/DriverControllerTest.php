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

/*
[
    {
        "id": 2,
        "game": "Pagac-Kiehn",
        "season": 0.28,
        "tier": 4.93,
        "name": "neque quia molestiae",
        "series": 17,
        "constructors": [
            {
                "id": 28,
                "name": "Howell Inc",
                "team": {
                    "id": 28,
                    "name": "Howell Inc",
                    "official": "#e7da90",
                    "game": null,
                    "logo": "https:\/\/cdn.discordapp.com\/attachments\/635742492192669696\/939176367159910470\/1.png",
                    "car": "https:\/\/www.f1gamesetup.com\/img\/teams\/2021\/ferrari-2021.png",
                    "created_at": "1996-01-27 22:01:06",
                    "updated_at": null,
                    "series": 30,
                    "title": "Dooley and Sons"
                },
                "start": 0,
                "flaps": 0,
                "points": 5,
                "penalties": 0,
                "end": 1
            },
            {
                "id": 29,
                "name": "Welch Ltd",
                "team": {
                    "id": 29,
                    "name": "Welch Ltd",
                    "official": null,
                    "game": null,
                    "logo": "https:\/\/cdn.discordapp.com\/attachments\/635742492192669696\/939176367159910470\/1.png",
                    "car": null,
                    "created_at": "1996-07-14 17:25:18",
                    "updated_at": null,
                    "series": 31,
                    "title": "Metz Ltd"
                },
                "start": 1,
                "flaps": 0,
                "points": 2,
                "penalties": 0,
                "end": 2
            },
            {
                "id": 30,
                "name": "Corwin, Glover and VonRueden",
                "team": {
                    "id": 30,
                    "name": "Corwin, Glover and VonRueden",
                    "official": null,
                    "game": "magni",
                    "logo": null,
                    "car": null,
                    "created_at": null,
                    "updated_at": null,
                    "series": 32,
                    "title": "Bode-Sawayn"
                },
                "start": 2,
                "flaps": 0,
                "points": 1,
                "penalties": 0,
                "end": 3
            }
        ],
        "tttracks": [
            {
                "id": 5,
                "name": "Turkmenistan Grand Prix",
                "location": null,
                "country": "Albania",
                "official": "Virginia",
                "display": null,
                "created_at": "1971-05-17 09:12:29",
                "updated_at": "2018-03-03 23:48:24",
                "track_length": "0.257 km",
                "laps": 4272909,
                "flag": null,
                "game": "voluptatibus",
                "series": 17,
                "title": "Metz, Rempel and Emmerich"
            },
            {
                "id": 6,
                "name": "Aruba Grand Prix",
                "location": null,
                "country": "South Africa",
                "official": "South Carolina",
                "display": null,
                "created_at": "2012-02-25 15:31:33",
                "updated_at": "1999-11-24 13:07:45",
                "track_length": "382333.117 km",
                "laps": 95841826,
                "flag": "https:\/\/www.formula1.com\/content\/dam\/fom-website\/2018-redesign-assets\/Flags%2016x9\/india-flag.png.transform\/9col\/image.png",
                "game": null,
                "series": 17,
                "title": "Grant-Franecki"
            },
            {
                "id": 7,
                "name": "Indonesia Grand Prix",
                "location": "Framiton",
                "country": "El Salvador",
                "official": "Kansas",
                "display": null,
                "created_at": "2000-10-09 20:47:55",
                "updated_at": "1992-05-08 02:07:52",
                "track_length": "26.993 km",
                "laps": 6,
                "flag": null,
                "game": "assumenda",
                "series": 17,
                "title": "Kling-O'Conner"
            }
        ],
        "drivers": [
            {
                "id": 5,
                "name": "osvaldo.nitzsche",
                "team": {
                    "id": 28,
                    "name": "Howell Inc",
                    "official": "#e7da90",
                    "game": null,
                    "logo": "https:\/\/cdn.discordapp.com\/attachments\/635742492192669696\/939176367159910470\/1.png",
                    "car": "https:\/\/www.f1gamesetup.com\/img\/teams\/2021\/ferrari-2021.png",
                    "created_at": "1996-01-27 22:01:06",
                    "updated_at": null,
                    "series": 30,
                    "title": "Dooley and Sons"
                },
                "start": 0,
                "flaps": 0,
                "points": 5,
                "penalties": 0,
                "end": 1,
                "status": 0,
                "user": 5
            },
            {
                "id": 6,
                "name": "wbailey",
                "team": {
                    "id": 29,
                    "name": "Welch Ltd",
                    "official": null,
                    "game": null,
                    "logo": "https:\/\/cdn.discordapp.com\/attachments\/635742492192669696\/939176367159910470\/1.png",
                    "car": null,
                    "created_at": "1996-07-14 17:25:18",
                    "updated_at": null,
                    "series": 31,
                    "title": "Metz Ltd"
                },
                "start": 1,
                "flaps": 0,
                "points": 2,
                "penalties": 0,
                "end": 2,
                "status": 0,
                "user": 6
            },
            {
                "id": 7,
                "name": "wwaelchi",
                "team": {
                    "id": 30,
                    "name": "Corwin, Glover and VonRueden",
                    "official": null,
                    "game": "magni",
                    "logo": null,
                    "car": null,
                    "created_at": null,
                    "updated_at": null,
                    "series": 32,
                    "title": "Bode-Sawayn"
                },
                "start": 2,
                "flaps": 0,
                "points": 1,
                "penalties": 0,
                "end": 3,
                "status": 0,
                "user": 7
            }
        ]
    }
]
*/
