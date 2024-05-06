<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Http\Requests\RaceResults;
use Illuminate\Database\Eloquent\Builder;
use App\Race;
use App\Result;
use App\Driver;
use App\Points;
use App\Series;
use App\Season;
use App\Discord;
use App\Circuit;
use App\Constructor;

class ResultsController extends Controller
{
    private $output;
    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    public function updatePosition(Request $request)
    {
        $request->validate([
            'newPos' => 'required|integer|gt:0',
            'driverid' => 'required|integer|gt:0',
            'raceid' => 'required|integer|gt:0'
        ]);

        $newPos = $request->newPos;
        $results = Result::where('race_id', $request->raceid)
                         ->orderBy('position')
                         ->get();

        if ($request->newPos > count($results)) {
            return -1;
        }

        $race_arr = json_decode($results, true);
        $driver_ind = array_search($request->driverid, array_column($race_arr, "driver_id"));
        if ($driver_ind === null) {
            return -1;
        }

        $oldPos = $results[$driver_ind]['position'];

        if ($request->has('status')) {
            $results[$oldPos - 1]['status'] = $request->status;
        }
        if ($request->has('newTime')) {
            $results[$oldPos - 1]['time'] = $request->newTime;
        }

        $results[$oldPos - 1]['position'] = $newPos;
        // $results[$oldPos - 1]['time'] = $request->newTime;
        $results[$oldPos - 1]->save();
        if ($newPos > $oldPos) {
            for ($i = $oldPos; $i < $newPos; $i++) {
                $results[$i]['position'] = $results[$i]['position'] - 1;
                $results[$i]->save();
            }
        } elseif ($newPos < $oldPos) {
            for ($i = $newPos - 1; $i < $oldPos - 1; $i++) {
                $results[$i]['position'] = $results[$i]['position'] + 1;
                $results[$i]->save();
            }
        }

        return $results;
    }

    public function saveRaceResults(RaceResults $request)
    {
        // Race Storing
        $track = new Race($request->validated()['track']);
        $race = $track->insertRace();

        // Result Storing
        $results = $request->validated()['results'];
        $regex_time = '/^\+?(\d+\:)?[0-5]?\d[.]\d{3}$|^DNF$|^DSQ$|^DNS$|^\+1 Lap$|^\+[2-9][0-9]* Laps$|^\-$/';
        $regex_fltime = '/^(\d+\:)?[0-5]?\d[.]\d{3}$|^\-$/';

        for ($i = 0; $i < count($results); $i++) {
            // Need to search from Driver List instead.
            if ($results[$i]['driver_id'] == '-1') {
                return response()->json([
                    "mesage" => "Error found in Driver ID",
                    "error" => $results[$i],
                ], 400);
            }

            if ($results[$i]['constructor_id'] == '-1') {
                return response()->json([
                    "mesage" => "Error found in Constructor ID",
                    "error" => $results[$i],
                ], 400);
            }

            $check = preg_match($regex_time, $results[$i]['time']);
            if ($check == '0') {
                return response()->json([
                    "message" => "Error found in Time format",
                    "error" => $results[$i],
                ], 400);
            }

            $check = preg_match($regex_fltime, $results[$i]['fastestlaptime']);
            if ($check == '0') {
                return response()->json([
                    "message" => "Error found in Fastest Lap Time format",
                    "error" => $results[$i],
                ], 400);
            }
        }

        foreach ($results as $k => $res) {
            if (array_key_exists('driver', $res)) {
                Driver::selfLearn($res['driver'], $res['driver_id']);
            }

            $res['race_id'] = $race['id'];
            $result = new Result($res);
            $returned_result = $result->storeResult();
            $results[$k] = $returned_result;
        }

        // Update Season Report Window & Reportable
        $season = Season::where('id', $race->season_id)->first();
        if ($season->report_window != null) {
            // Advance report_window by 1 Week until it goes over Current Time
            while (strtotime($season->report_window) < time()) {
                $season->report_window = date('Y-m-d H:i:s', strtotime($season->report_window) + 604800);
            }

            $season->save();

            // Publish Report Splitter Message
            if ($season->report_channel != null) {
                $message = " **-----------------------------**\n       Round ";
                $message .= $race->round . " Reports\n **-----------------------------**";
                Discord::publishMessage($message, $season->report_channel);
            }
        }

        return response()->json([
            "race" => $race,
            "result" => $results
        ]);
    }

    protected function surroundingRaces($seasonid, $round)
    {
        $prevRace = Race::has('results')
                        ->where([
                            ['season_id', $seasonid],
                            ['round', ($round - 1)]
                        ])
                        ->first();
        $nextRace = Race::has('results')
                        ->where([
                            ['season_id', $seasonid],
                            ['round', ($round + 1)]
                          ])
                        ->first();

        if ($prevRace) {
            $prevRace->load('circuit');
        }
        if ($nextRace) {
            $nextRace->load('circuit');
        }

        return array($prevRace, $nextRace);
    }

    public function fetchRaceResults($code, $tier, $season, $round)
    {
        $series = Series::where("code", $code)->firstOrFail();
        $season = Season::where([
            ['series', $series['id']],
            ['tier', $tier],
            ['season', $season]
        ])->firstOrFail();

        $points = Points::all()->toArray();
        $race = Race::where('season_id', $season['id'])
            //    whereHas('season',
            // function (Builder $query) use ($series, $tier, $season) {
            //    $query->where([
            //        ['series', $series['id']],
            //        ['tier', $tier],
            //        ['season', $season]
            //    ]);
            // })
                    ->where('round', $round)
                    ->firstOrFail();

        $results = Result::where('race_id', $race['id'])
                         ->orderBy('position', 'asc')
                         ->get()
                         ->load('driver', 'race.circuit', 'constructor:id,name')
                         ->toArray();

        if (count($results) > 0) {
            $cl = $results[0]['race']['circuit']['laps'] * $results[0]['race']['distance'];
            $results[0]['race']['circuit']['laps'] = ceil($cl);
        }

        foreach ($results as $i => $res) {
            $pos = $res['position'];
            $results[$i]['points'] = $res['points'];

            if ($res['status'] >= 0) {
                $rpoints = 0;
                $ps_ind = array_search($results[$i]['race']['points'], array_column($points, "id"));
                if (array_key_exists('P' . $pos, $points[$ps_ind])) {
                    $rpoints = $points[$ps_ind]['P' . $pos];
                }

                $results[$i]['points'] += $rpoints;
                if (((int)abs($results[$i]['status']) % 10) == 1 && $rpoints > 0) {
                    $results[$i]['points'] += 1;
                }
            }
        }

        $sr = $this->surroundingRaces($season['id'], $round);
        $count = count($results);

        return view('standings.race')
                ->with('code', $code)
                ->with('tier', $tier)
                ->with('season', $season['season'])

                ->with('prevRace', $sr[0])
                ->with('nextRace', $sr[1])
                ->with('results', $results)
                ->with('count', $count);
    }

    public function uploadResults()
    {
        $season = Season::active()->get()->load('series');
        $points = Points::all();
        $tracks = Circuit::select('id', 'name', 'series')->get()->load('series');
        $constructor = Constructor::select('id', 'name')->get();
        $driver = Driver::select('id', 'name')->get();

        return view('standings.upload')
               ->with('season', $season)
               ->with('points', $points)
               ->with('tracks', $tracks)
               ->with('constructor', $constructor)
               ->with('driver', $driver);
    }

    public function fetchResultsByRaceId($race)
    {
        $track = Race::findOrFail($race);
        $results = Result::where('race_id', $race)
                         ->orderBy('position', 'asc')
                         ->get()->toArray();

        return response()->json([
            "track" => $track,
            "results" => $results
        ]);
    }
}
