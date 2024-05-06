<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Input;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\User;
use App\Driver;
use App\Season;
use App\Series;
use App\Points;
use App\Circuit;
use App\Constructor;

class AcController extends ImageController
{
    private $output;
    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    // View for CSV Upload
    public function raceUpload()
    {
        $series = Series::where('code', 'ac')->firstOrFail();
        $seasons = Season::active()->where('series', $series['id'])->get();

        $points = Points::all();

        return view('acupload')
               ->with('points', $points)
               ->with('seasons', $seasons);
    }

    // Currently the CSV is generated from an SQLite Query
    // The Query HAS to be run on a Unix system

    // TODO: Handle Windows Line Ending format
    // TODO: Validate File Format and Columns
    public function parseCsv(Request $request)
    {
        $race = request()->file('race');
        $rcsvlines = file_get_contents($race);

        $rcsv = str_getcsv($rcsvlines, "\n");
        if (count($rcsv) == 0) {
            return response()->json([]);
        }

        $minid = 0;
        $mintime = PHP_INT_MAX;
        for ($j = 0; $j < count($rcsv); $j++) {
            $l = str_replace("\"", "", $rcsv[$j]);
            $rcsv[$j] = explode(",", $l);

            $rcsv[$j][0] = (int)$rcsv[$j][0]; // Finishing Position, Name, Car
            $rcsv[$j][3] = (int)$rcsv[$j][3]; // Fastest Lap
            $rcsv[$j][4] = (int)$rcsv[$j][4]; // Total Time
            $rcsv[$j][5] = (int)$rcsv[$j][5]; // Grid, Track, Laps

            // Check which driver has fastest lap
            if ($rcsv[$j][5] < $mintime) {
                $minid = $j;
                $mintime = $rcsv[$j][5];
            }
        }

        $round = (int)request()->round;
        $points = (int)request()->points;
        $season = Season::find(request()->season);
        $sp_circuit = Circuit::game($rcsv[0][6], $season['series'])->first();
        if ($sp_circuit == null) {
            return response()->json([]);
        }

        $track = array(
            'circuit_id' => $sp_circuit['id'],
            'official' => $sp_circuit['official'],
            'display' => $sp_circuit['name'],
            "season_id" => $season['id'],
            "distance" => $rcsv[0][7] / 10.0,
            "points" => (int)$points,
            "round" => $round
        );

        $results = array();
        // Check for Fastest Time, get ID

        $drList = Driver::getNames();
        foreach ($rcsv as $k => $driver) {
            // Search for Closest Matching Driver
            $drName = array_column($drList, 'name');

            $index = $this->closestMatch($driver[1], $drName);
            $flat_drivers = $this->crudeFlatten((array)$drList);

            $matched_driverid = $drList[$index[0]]['id'];
            $matched_drivername = $drList[$index[0]]['name'];

            if ($index[1] != 0) {
                $fname = array_column($flat_drivers, 'alias');
                $findex = $this->closestMatch($driver[1], $fname);

                if ($findex[1] < $index[1]) {
                    $matched_driverid = $flat_drivers[$findex[0]]['id'];
                    $matched_drivername = $flat_drivers[$findex[0]]['alias'];
                }
            }

            // Search Car
            $status = 0;
            $car = Constructor::where('game', $driver[2])->first();
            if ($car == null) {
                $car = array("id" => -1, "name" => "NA");
            }

            // if position > 1000, position -= 1000;
            // if minid (fastest lap), $status = 1;
            if ($driver[0] > 1000) {
                $status = -2;
                $driver[0] -= 1000;
            } elseif ($k == $minid) {
                $status = 1;
            }

            // Convert Times to Standard Format
            $fastestLapTime = $this->convertMillisToStandard($driver[3]);
            if ($fastestLapTime == "00") {
                $fastestLapTime = "-";
            }

            $totalTime = $this->convertMillisToStandard($driver[4]);
            if ($totalTime == "00") {
                $totalTime = "DNF";
            }

            // Push to Results
            array_push($results, array(
                "position" => (int)$driver[0],
                "driver" => $driver[1],
                "driver_id" => (int)$matched_driverid,
                "matched_driver" => $matched_drivername,
                "team" => $car['name'],
                "constructor_id" => (int)$car['id'],
                "matched_team" => $car['name'],
                "grid" => ($driver[5] > 1000) ? $driver[5] - 1000 : $driver[5],
                "stops" => (int)$driver[7],
                "status" => $status,
                "fastestlaptime" => $fastestLapTime,
                "time" => $totalTime
            ));
        }

        return response()->json(["track" => $track, "results" => $results]);
    }

    private function memoizeLaps($json)
    {
        $res = array();

        // Iterate through Cars
        foreach ($json['Cars'] as $k => $cars) {
            $res[$cars["CarId"]] = 0;
        }

        // Iterate through Laps and add models
        foreach ($json['Laps'] as $k => $laps) {
            $res[$laps["CarId"]]++;
        }

        return $res;
    }

    public function parseJson(Request $request)
    {
        $race = request()->file('race');
        $race_content = file_get_contents($race);
        $json = json_decode($race_content, true);

        $round = (int)request()->round;
        $points = (int)request()->points;
        $season = Season::find(request()->season);
        $seasonConstructors = $season->constructors->toArray();

        $sp_circuit = Circuit::game($json['TrackName'], $season['series'])->first();
        if ($sp_circuit == null) {
            return response()->json([]);
        }

        // Total Lap Calculation
        $lapsMap = $this->memoizeLaps($json);
        $totalLaps = $lapsMap[$json['Result'][0]['CarId']];

        $results = array();
        $track = array(
            'circuit_id' => $sp_circuit['id'],
            'official' => $sp_circuit['official'],
            'display' => $sp_circuit['name'],
            "season_id" => $season['id'],
            "distance" => $totalLaps / 10.0,
            "points" => (int)$points,
            "round" => $round
        );

        // Cycle through Results
        foreach ($json['Result'] as $k => $driver) {
            if ($driver['DriverName'] == "") {
                break;
            }

            $user = User::where('steam_id', $driver['DriverGuid'])->first();
            $dr = Driver::where('user_id', $user['id'])->first();
            if ($dr == null) {
                $dr['name'] = $driver['DriverName'];
                $dr['id'] = -1;
            }

            $grid = 0;
            $status = 0;
            $total_time = "";
            $bestLap = "";

            $team_ind = 2;

            // Total Time
            // if($totalLaps == $driver['timing']['lapCount'])
            if ($driver['TotalTime'] == 0) {
                $status = -2;
                $total_time = "DNF";
            } else {
                $total_time = $this->convertMillisToStandard($driver['TotalTime']);
            }
            // else
            // {
            //     $total_time = "+" . ($totalLaps - $driver['timing']['lapCount']) . " Lap";
            //     if($totalLaps - $driver['timing']['lapCount'] > 1)
            //         $total_time .= "s";
            // }

            if ($driver['BestLap'] == 999999999) {
                $bestLap = "-";
            } else {
                $bestLap = $this->convertMillisToStandard($driver['BestLap']);
            }

            // Push to Results
            array_push($results, array(
                "position" => $k + 1,
                "driver" => $dr['name'],
                "driver_id" => $dr['id'],
                "matched_driver" => $dr['name'],
                "team" => $seasonConstructors[$team_ind]['name'],
                "constructor_id" => $seasonConstructors[$team_ind]['id'],
                "matched_team" => $seasonConstructors[$team_ind]['name'],
                "grid" => $grid,
                "stops" => $lapsMap[$driver['CarId']],
                "status" => $status,
                "fastestlaptime" => $bestLap,
                "time" => $total_time
            ));
        }

        return response()->json(["track" => $track, "results" => $results]);
    }
}
