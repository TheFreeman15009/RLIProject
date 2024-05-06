<?php

namespace App\Http\Controllers;

use App\User;
use App\Driver;
use App\Season;
use App\Series;
use App\Points;
use App\Circuit;
use App\Http\Requests\AccResultFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AccResultParseRequest;
use Symfony\Component\Console\Output\ConsoleOutput;

// ACC Result Parsing Controller Class
class AccController extends Controller
{
    private $output;
    private const DNF_TIME = 2147483647;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    // View to Upload Race Results
    public function raceUpload()
    {
        $series = Series::where('code', 'acc')->firstOrFail();
        $seasons = Season::active()->where('series', $series['id'])->get();

        $points = Points::all();

        return view('accupload')
               ->with('points', $points)
               ->with('seasons', $seasons);
    }

    private function generateTrack(&$season, &$request, &$json)
    {
        $sp_circuit = (request()->has('db_circuitTrackName')) ? request('db_circuitTrackName') :
                        Circuit::game($json['trackName'], $season['series'])->first();

        $totalLaps = 0;
        if (count($json['sessionResult']['leaderBoardLines']) > 0) {
            $totalLaps = $json['sessionResult']['leaderBoardLines'][0]['timing']['lapCount'];
        }

        return array(
            'circuit_id' => $sp_circuit['id'],
            'official' => $sp_circuit['official'],
            'display' => $sp_circuit['name'],
            "season_id" => $season['id'],
            "distance" => $totalLaps / 10.0,
            "points" => (int)$request->points,
            "round" => (int)$request->round
        );
    }

    private function computeGrid($mode, &$driver, &$qualiPosition)
    {
        if ($mode) {
            if (array_key_exists($driver['currentDriver']['playerId'], $qualiPosition)) {
                return $qualiPosition[$driver['currentDriver']['playerId']];
            }
        } elseif (array_key_exists($driver['car']['carId'], $qualiPosition)) {
            return $qualiPosition[$driver['car']['carId']];
        }

        return 0;
    }
    private function computeStatus(&$driver, &$json, $k)
    {
        $status = 0;
        if ($json['sessionResult']['bestlap'] == $driver['timing']['bestLap'] && $k < 10) {
            $status = 1;
        }
        if ($driver['timing']['lastLap'] == self::DNF_TIME) {
            $status = -2;
        }

        return $status;
    }
    private function computeBestLap(&$driver)
    {
        return ($driver['timing']['bestLap'] == self::DNF_TIME) ? '-' :
                    $this->convertMillisToStandard($driver['timing']['bestLap']);
    }
    private function computeTotalTime(&$driver)
    {
        return ($driver['timing']['lastLap'] == self::DNF_TIME) ? 'DNF' :
                    $this->convertMillisToStandard($driver['timing']['totalTime']);
    }


    private function generateResults(&$season, $mode, &$json, &$jq)
    {
        $results = array();
        $qualiPosition = array();
        $userSteamIds = array();
        foreach ($jq['sessionResult']['leaderBoardLines'] as $k => $driver) {
            array_push($userSteamIds, substr($driver['currentDriver']['playerId'], 1));

            if ($mode) {
                // Multi Session, Single Driver Setup
                if (!array_key_exists($driver['currentDriver']['playerId'], $qualiPosition)) {
                    $qualiPosition[$driver['currentDriver']['playerId']] = $k + 1;
                }
            } else {
                // Single Session, Multi Driver Setup
                $qualiPosition[$driver['car']['carId']] = $k + 1;
            }
        }

        $users = User::select('id', 'steam_id')->whereIn('steam_id', $userSteamIds)->get();
        $userIds = $users->pluck('id')->toArray();
        $users = $users->toArray();

        $dr = Driver::select('id', 'name', 'user_id')->whereIn('user_id', $userIds)->get()->toArray();

        foreach ($json['sessionResult']['leaderBoardLines'] as $k => $driver) {
            $driverId = -1;
            $driverName = $driver['currentDriver']['shortName'];

            $user_ind = array_search(substr($driver['currentDriver']['playerId'], 1), array_column($users, 'steam_id'));
            if ($user_ind !== false) {
                $dr_ind = array_search($users[$user_ind]['id'], array_column($dr, 'user_id'));
                if ($dr_ind !== false) {
                    $driverId = $dr[$dr_ind]['id'];
                    $driverName = $dr[$dr_ind]['name'];
                }
            }

            $seasonConstructors = $season->constructors->toArray();
            $team_ind = array_search($driver['car']['carModel'], array_column($seasonConstructors, "game"));

            // Push to Results
            array_push($results, array(
                "position" => $k + 1,
                "driver" => $driverName,
                "driver_id" => $driverId,
                "stops" => $driver['timing']['lapCount'],
                "time" => $this->computeTotalTime($driver),
                "team" => $seasonConstructors[$team_ind]['name'],
                "fastestlaptime" => $this->computeBestLap($driver),
                "status" => $this->computeStatus($driver, $json, $k),
                "constructor_id" => $seasonConstructors[$team_ind]['id'],
                "grid" => $this->computeGrid($mode, $driver, $qualiPosition)
            ));
        }

        return $results;
    }

    private function loadFile(&$request, $session)
    {
        $sessionFile = $request->file($session);
        $sessionContent = file_get_contents($sessionFile);

        return json_decode($sessionContent, true);
    }

    // ACC Result File must be in UTF-8 encoding
    public function parseJson(AccResultParseRequest $request)
    {
        $jq = $this->loadFile($request, 'quali');
        $json = $this->loadFile($request, 'race');

        $season = (request()->has('db_season')) ? request('db_season') : Season::find($request->season);

        $accRuleSet = new AccResultFile();
        Validator::make($json, $accRuleSet->rules($season))->validate();

        return response()->json([
            "track" => $this->generateTrack($season, $request, $json),
            "results" => $this->generateResults($season, $request->mode, $json, $jq)
        ]);
    }
}
