<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Driver;
use App\Result;
use App\Season;
use App\Race;
use App\Circuit;
use App\Points;
use App\Series;

class StandingsController extends Controller
{
    public function fetchDrivers()
    {
        $query = Driver::all();
        return $query;
    }

    public function fetchCircuit()
    {
        $query = Circuit::all();
        return $query;
    }

    public function fetchRaces($code, $tier, $season)
    {
        $series = Series::code($code)->firstOrFail();
        $season = Season::where([
            ['series_id', $series['id']],
            ['tier', $tier],
            ['season', $season]
        ])->firstOrFail();

        $cs = $this->computeStandings($series['id'], $tier, $season['season']);
        if ($cs['code'] != 200) {
            return abort(404);
        }

        $races = Race::where('season_id', $season['id'])
                     ->has('results')
                     ->orderBy('round', 'asc')
                     ->get()->load('season', 'circuit');

                       // whereHas('season', function (Builder $query) use ($tier, $season) {
                       //     $query->where([
                       //         ['tier', $tier],
                       //         ['season', $season]
                       //     ]);
                       // })
                       // ->orderBy('round', 'asc')
                       // ->get()->load('season','circuit');

        return view('standings.allraces')
                ->with('code', $code)

                ->with('flaps', $cs['flaps'])
                ->with('penalties', $cs['penalties'])

                ->with('tdrivers', $cs['drivers'])
                ->with('tconst', $cs['constructors'])

                ->with('races', $races)
                ->with('season', $season);
    }

    protected function latestRace($array, $start, $end)
    {
        $max = $array[$start]['race']['round'];
        $maxi = $start;
        for ($i = $start + 1; $i < $end; $i++) {
            $cur = $array[$i]['race']['round'];
            if ($cur > $max) {
                $max = $cur;
                $maxi = $i;
            }
        }

        return $maxi;
    }

    protected function computeStandings($series, $tier, $season)
    {
        $season = Season::where([
            ['series_id', $series],
            ['tier', $tier],
            ['season', $season]
        ])->first();
        if ($season == null) {
            return array("code" => 404);
        }

        $psystem = Points::all()->toArray();
        $races = Race::where('season_id', $season['id'])
                     ->pluck("id");
        if (!count($races)) {
            return array("code" => 404);
        }

        $results = Result::whereIn('race_id', $races)
                         ->orderBy('driver_id')
                         ->orderBy('position')
                         ->get()
                         ->load('driver:id,name,user_id', 'constructor', 'race')
                         ->toArray();
        if (!count($results)) {
            return array("code" => 404);
        }

        $countReserves = 0;
        $flaps = array();
        $penalties = array();
        $dres = $this->computePoints($results, 'driver', $psystem);

        for ($i = 0; $i < count($dres); $i++) {
            $ind = $this->latestRace($results, $dres[$i]['start'], $dres[$i]['end']);
            $dres[$i]['team'] = $results[$ind]['constructor'];
            $dres[$i]['status'] = $results[$ind]['status'];
            $dres[$i]['user'] = $results[$ind]['driver']['user_id'];

            if ($dres[$i]['flaps'] > 0) {
                array_push($flaps, array("name" => $dres[$i]["name"],
                                         "flaps" => $dres[$i]["flaps"]));
            }
            if ($dres[$i]['penalties'] > 0) {
                array_push($penalties, array("name" => $dres[$i]["name"],
                                             "penalties" => $dres[$i]["penalties"]));
            }

            if (
                (abs($dres[$i]['status']) >= 10 && abs($dres[$i]['status']) < 20)
                    || ($dres[$i]['team']['name'] == 'Reserve')
            ) {
                $countReserves++;
            }
        }

        $this->sortByKey($flaps, "flaps", -1);
        $this->sortByKey($penalties, "penalties", -1);

        $cres = $this->computePoints($results, 'constructor', $psystem);
        return array(
            "code" => 200,
            "flaps" => $flaps,
            "drivers" => $dres,
            "penalties" => $penalties,
            "countReserves" => $countReserves,
            "constructors" => $cres,
            "season" => $season
        );
    }

    public function fetchStandings($code, $tier, $season)
    {
        $series = Series::code($code)->first();
        if ($series == null) {
            return abort(404);
        }

        $cs = $this->computeStandings($series['id'], $tier, $season);
        if ($cs['code'] != 200) {
            return abort(404);
        }

        $latestRound = Race::where('season_id', $cs['season']['id'])
                           ->max('round');

        $nextRace = $this->nextRace($cs['season']['id']);

        return view('standings.season')
               ->with('code', $code)

               ->with('flaps', $cs['flaps'])
               ->with('penalties', $cs['penalties'])

               ->with('res', $cs['drivers'])
               ->with('count', count($cs['drivers']))
               ->with('reservecount', $cs['countReserves'])

               ->with('cres', $cs['constructors'])
               ->with('ccount', count($cs['constructors']))

               ->with('season', $cs['season'])
               ->with('latestRound', $latestRound)
               ->with('nextRace', $nextRace);
    }

    protected function nextRace($seasonid)
    {
        // $season = Season::where([
        //    ['tier', $tier],
        //    ['season', $season]
        // ])->firstOrFail();

        $round = Race::has('results')
                      ->where('season_id', $seasonid)
                      ->max('round');

        $nextRace = Race::where([
                        ['season_id', $seasonid],
                        ['round', ($round + 1)]
                    ])->first();

        if ($nextRace) {
            $circuit = Circuit::find($nextRace['circuit_id']);
            $circuit['laps'] = ceil($circuit['laps'] * $nextRace['distance']);
            return $circuit;
        }
        return $nextRace;
    }

    protected function sortPos($a, $b)
    {
        if ($a['position'] > $b['position']) {
            return 1;
        } elseif ($a['position'] < $b['position']) {
            return -1;
        } else {
            return 0;
        }
    }
    protected function computePoints($results, string $field, $psystem)
    {
        // Sort $results by $field
        $this->sortByKey($results, $field . '_id');

        $prev = $results[0][$field . '_id'];
        $dres = array(array(
            "id" => $results[0][$field . '_id'],
            "name" => $results[0][$field]['name'],
            "team" => $results[0]['constructor'],
            "start" => 0
        ));

        $points = 0;
        $flaps = 0;
        $penalties = 0;
        foreach ($results as $k => $driver) {
            if ($prev != $driver[$field . '_id']) {
                $cur = array(
                    "id" => $driver[$field . '_id'],
                    "name" => $driver[$field]['name'],
                    "team" => $driver['constructor'],
                    "start" => $k
                );

                $dres[count($dres) - 1]['flaps'] = $flaps;
                $dres[count($dres) - 1]['points'] = $points;
                $dres[count($dres) - 1]['penalties'] = $penalties;
                $dres[count($dres) - 1]['end'] = $k;
                $flaps = 0;
                $points = 0;
                $penalties = 0;
                array_push($dres, $cur);
            }

            $pos = $driver['position'];
            $prev = $driver[$field . '_id'];
            $penalties += round((abs($driver['status']) - (int)abs($driver['status'])) * 10, 2);
            $points += $driver['points'];
            if ($driver['status'] >= 0) {
                $rpoints = 0;
                $ps_ind = array_search($results[$k]['race']['points_id'], array_column($psystem, "id"));
                if (array_key_exists('P' . $pos, $psystem[$ps_ind])) {
                    $rpoints = $psystem[$ps_ind]['P' . $pos];
                }

                $points += $rpoints;
                if (((int)abs($driver['status']) % 10) == 1) {
                    $flaps += 1;
                    if ($rpoints > 0) {
                        $points += 1;
                    }
                }
            }
        }
        $dres[count($dres) - 1]['flaps'] = $flaps;
        $dres[count($dres) - 1]['points'] = $points;
        $dres[count($dres) - 1]['penalties'] = $penalties;
        $dres[count($dres) - 1]['end'] = count($results);

        usort($dres, function ($a, $b) use ($results) {
            if ($a['points'] < $b['points']) {
                return 1;
            } elseif ($a['points'] > $b['points']) {
                return -1;
            }

            $l = array_slice($results, $a['start'], $a['end'] - $a['start']);
            $r = array_slice($results, $b['start'], $b['end'] - $b['start']);
            usort($l, array($this, "sortPos"));
            usort($r, array($this, "sortPos"));

            // Equal Points
            for ($j = 0; $j < min(count($l), count($r)); $j++) {
                if ($l[$j]['position'] > $r[$j]['position']) {
                    return 1;
                }
                if ($l[$j]['position'] < $r[$j]['position']) {
                    return -1;
                }
            }

            if (count($l) < count($r)) {
                return 1;
            } elseif (count($l) < count($r)) {
                return -1;
            } else {
                return 0;
            }
        });
        return $dres;
    }

    public function storeResults(Request $request)
    {
        $result = new Result();
        $result->race_id = $request['race_id'];
        $result->constructor_id = $request['constructor_id'];
        $result->driver_id = $request['driver_id'];
        $result->grid = $request['grid'];
        $result->points = $request['points'];
        $result->status = $request['status'];
        $result->fastestlaptime = $request['fastestlaptime'];
        $result->tyres = $request['tyres'];
        $result->position = $request['position'];
        $result->save();

        return redirect('/');
    }
}
