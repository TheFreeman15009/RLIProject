<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Driver;
use App\Report;
use App\Season;
use App\Series;
use App\Constructor;

class DriverController extends StandingsController
{
    public function viewusers()
    {
        $users = User::select('id', 'name', 'avatar', 'updated_at')->get()->sortByDesc('updated_at');
        return view('admin.viewusers')->with('user', $users);
    }

    public function viewreports(Report $report)
    {
        $reports = Report::where('resolved', '>', -1)
                       ->orderBy('resolved', 'asc')
                       ->orderBy('created_at', 'desc')->get();

        return view('admin.reports')->with('report', $reports);
    }

    public function reportdetails(Report $report)
    {
        return view('admin.reportdetails')->with('report', $report);
    }

    public function saveverdict(Report $report)
    {
        $data = request()->all();
        $report->verdict = $data['verdict'];
        $report->resolved = 1;
        $report->save();
        return redirect()->back();
    }

    public function allotuser(User $id)
    {
        $existing = Driver::where('user_id', $id->id)->get();
        return view('admin.allot')
              ->with('user', $id)
              ->with('existing', $existing);
    }

    public function saveallotment(Request $request)
    {
        $userName = User::find($request->user_id)->select('name')->first()->name;

        $driver = new Driver();
        $driver->user_id = $request->user_id;
        $driver->name = $userName;
        $driver->drivernumber = 5;
        $driver->retired = 0;
        $driver->alias = $userName;
        $driver->tier = $request->tier;
        $driver->save();

        return redirect()->back();
    }

    public function driverData()
    {
        $driver = Driver::select('id', 'name', 'alias', 'drivernumber', 'user_id')
                        ->get()
                        ->load('user:id,avatar,discord_id,steam_id,xbox,psn')
                        ->toArray();

        $series = Series::select('id', 'name', 'code', 'games')->get()->toArray();
        $constructor = Constructor::all()->toArray();

        $seasons = Season::where('status', '<', 2)->get()->toArray();
        $ts = array();

        // Iterate through all Active Seasons
        for ($i = 0; $i < count($seasons); ++$i) {
            for ($j = 0; $j < count($driver); ++$j) {
                $driver[$j]['season_points'][$seasons[$i]['id']] = 0;
            }
            for ($j = 0; $j < count($constructor); ++$j) {
                $constructor[$j]['season_points'][$seasons[$i]['id']] = 0;
            }

            // Results for this Season
            $ts = $this->computeStandings($seasons[$i]['series'], $seasons[$i]['tier'], $seasons[$i]['season']);
            if ($ts['code'] != 200) {
                continue;
            }

            // Add Points to Drivers
            for ($j = 0; $j < count($ts['drivers']); ++$j) {
                $d_id = array_search($ts['drivers'][$j]['id'], array_column($driver, "id"));
                $driver[$d_id]['season_points'][$seasons[$i]['id']] = $ts['drivers'][$j]['points'];
            }

            // Add Points to Constructors
            for ($j = 0; $j < count($ts['constructors']); ++$j) {
                $c_id = array_search($ts['constructors'][$j]['id'], array_column($constructor, "id"));
                $constructor[$c_id]['season_points'][$seasons[$i]['id']] = $ts['constructors'][$j]['points'];
            }
        }

        /* Returns:
            {
                "drivers": [
                    {
                        "id": 1,
                        "name": "xyz",
                        "alias": [
                            "xyz",
                            "The XYZ"
                        ],
                        "drivernumber": 2,
                        "user_id": 1,
                        "user": {
                            "id": 1,
                            "avatar": "https://cdn.discordapp.com/avatars/.../def.jpg",
                            "discord_id": "111111",
                            "steam_id": "12222222",
                            "xbox": "xyzaa",
                            "psn": "xyzps"
                        },
                        "season_points": {
                            "<season_id>": 0
                        }
                    }

                ],

                "constructors: [
                    {
                        "id": 109,
                        "name": "XYZ Corp",
                        "official": "#0090ff",
                        "game": "5",
                        "logo": "https://cdn.discordapp.com/attachments/..../xyz.png",
                        "car": "https://www.formula1.com/content/dam/fom-website/..../abc.png",
                        "created_at": null,
                        "updated_at": null,
                        "series": 1,
                        "title": "2023",
                        "season_points": {
                            "<season_id>": 0
                        }
                    }
                ],

                "series": [
                    {
                        "id": 10,
                        "name": "F1",
                        "code": "f1",
                        "games": "F1 2020, F1 2021, F1 22, F1 23"
                    }
                ]
            }
        */
        return response()->json([
            "drivers" => $driver,
            "constructors" => $constructor,
            "series" => $series
        ]);
    }

    public function seasonData()
    {
        $driver = Driver::select('id', 'name', 'tier', 'team', 'drivernumber', 'user_id')
                    ->get()->load('user:id,name,avatar,steam_id,xbox')->toArray();

        $constructor = Constructor::all()->toArray();

        $seasons = Season::select('id', 'game', 'season', 'tier', 'name', 'series', 'constructors', 'tttracks')
                     ->where('status', '<', 2)->get()->toArray();

        // Iterate through all Active Seasons
        for ($i = 0; $i < count($seasons); ++$i) {
            // Results for this Season
            $ts = $this->computeStandings($seasons[$i]['series'], $seasons[$i]['tier'], $seasons[$i]['season']);
            if ($ts['code'] != 200) {
                continue;
            }

            $seasons[$i]['drivers'] = $ts['drivers'];
            $seasons[$i]['constructors'] = $ts['constructors'];
        }

        // Response schema is in Tests\Controllers\DriverControllerTest::testSeasonDataApi()
        return response()->json($seasons);
    }
}
