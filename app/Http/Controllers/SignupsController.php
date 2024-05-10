<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Season;
use App\Driver;
use App\Signup;
use App\Discord;
use Storage;
use Auth;

class SignupsController extends Controller
{
    public function view()
    {
        $signups = Signup::where('user_id', Auth::user()->id)->get()->toArray();

        $allseason = Season::all();
        $seasons = array();

        foreach ($allseason as $i => $particular_season) {
            $status = $particular_season->status;
            if ($status - (int)$status > 0) {
                array_push($seasons, $particular_season);
            }
        }

        if (count($seasons) == 0) {
            session()->flash('info', 'Hey, sorry! We are not currently accepting new signups for any ongoing seasons.');
            return redirect('/');
        }

        return view('signup.home')
             ->with('seasons', $seasons)
             ->with('signup', $signups);
    }

    public function store(Request $request)
    {
        $data = request()->all();
        if (isset($data['evidencet1']) & isset($data['evidencet2']) & isset($data['evidencet3'])) {
            if ($data['evidencet1'] != null & $data['evidencet2'] != null & $data['evidencet3'] != null) {
                $evidence1 = $data['evidencet1']->store('timetrials');
            }
              $evidence2 = $data['evidencet2']->store('timetrials');
              $evidence3 = $data['evidencet3']->store('timetrials');
        }
        if ($data['attendance'] == "YES") {
            $attendance = 1;
        } else {
            $attendance = 0;
        }
        if (isset($data['pref1'])) {
            $prefrence = $data['pref1'] . ',' . $data['pref2'] . ',' . $data['pref3'];
        }
        if (isset($data['assists'])) {
            $assists = serialize($data['assists']);
        } else {
            $assists = '';
        }

        $signup = new Signup();

        $signup->user_id = Auth::user()->id;
        $signup->season_id = $data['seas'];
        $signup->speedtest = $data['speedtest'];
        $statCheck = $data['statusCheck'] - floor($data['statusCheck']);
        $statCheck = round($statCheck, 1);
        if ($statCheck != 0.3) {
            $signup->timetrial1 = $data['t1'];
            $signup->timetrial2 = $data['t2'];
            $signup->timetrial3 = $data['t3'];
            $signup->ttevidence1 = $evidence1;
            $signup->ttevidence2 = $evidence2;
            $signup->ttevidence3 = $evidence3;
        } else {
            $signup->timetrial1 = '';
            $signup->timetrial2 = '';
            $signup->timetrial3 = '';
            $signup->ttevidence1 = '';
            $signup->ttevidence2 = '';
            $signup->ttevidence3 = '';
        }
        if (isset($prefrence)) {
            $signup->carprefrence = $prefrence;
        } else {
            $signup->carprefrence = '';
        }

        $signup->attendance = $attendance;
        $signup->assists = $assists;
        $signup->drivernumber = $data['drivernumber'];
        $signup->save();

        $discord = new Discord();
        $discord->notifysignup($data['seas']);
        session()->flash('success', "Signup Submitted Successfully");

        return redirect()->route('driver.signup');
    }

    public function update(Signup $signup)
    {
        $data = request()->all();

        if ($signup->user_id == Auth::user()->id) {
            if ($data['attendance'] == "YES") {
                $attendance = 1;
            } else {
                $attendance = 0;
            }
            if (isset($data['assists'])) {
                $assists = serialize($data['assists']);
            } else {
                $assists = '';
            }
            if (isset($data['pref1'])) {
                $prefrence = $data['pref1'] . ',' . $data['pref2'] . ',' . $data['pref3'];
                $signup->carprefrence = $prefrence;
            }

            $signup->season_id = $data['seas'];
            $signup->speedtest = $data['speedtest'];
            $statCheck = $data['statusCheck'] - floor($data['statusCheck']);
            $statCheck = round($statCheck, 1);
            if ($statCheck != 0.3) {
                $signup->timetrial1 = $data['t1'];
                $signup->timetrial2 = $data['t2'];
                $signup->timetrial3 = $data['t3'];
            } else {
                $signup->timetrial1 = '';
                $signup->timetrial2 = '';
                $signup->timetrial3 = '';
            }
            $signup->attendance = $attendance;

            $signup->assists = $assists;
            $signup->drivernumber = $data['drivernumber'];

            if (isset($data['evidencet1'])) {
                $evidence1 = $data['evidencet1']->store('timetrials');
                Storage::delete($signup->ttevidence1);
                $signup->ttevidence1 = $evidence1;
            }
            if (isset($data['evidencet2'])) {
                $evidence1 = $data['evidencet2']->store('timetrials');
                Storage::delete($signup->ttevidence2);
                $signup->ttevidence2 = $evidence1;
            }
            if (isset($data['evidencet3'])) {
                $evidence1 = $data['evidencet3']->store('timetrials');
                Storage::delete($signup->ttevidence3);
                $signup->ttevidence3 = $evidence1;
            }

            $signup->save();
            session()->flash('success', "Signup Updated Successfully");
            return redirect()->route('driver.signup');
        } else {
            return redirect('/');
        }
    }

    public function viewsignups()
    {
        $activeSeasons = Season::activeWithSignups()->get();
        $activeSeasonIds = $activeSeasons->pluck('id');
        $signedUsers = Signup::whereIn('season_id', $activeSeasonIds)
                      ->orderBy('updated_at', 'desc')->get()
                      ->load('user', 'season')->toArray();

        $seasonNamesWithSignups = [];
        foreach ($signedUsers as $user) {
            $seasonId = $user['season']['name'];
            if (!array_key_exists($seasonId, $seasonNamesWithSignups))
                $seasonNamesWithSignups[$seasonId] = 0;

            $seasonNamesWithSignups[$seasonId]++;
        }

        return view('admin.signups')
        ->with('data', $signedUsers)
        ->with('season', array_keys($seasonNamesWithSignups));
    }

    /**
     *  Signups API - Returns active seasons with their signups
     *  @return \Illuminate\Http\JsonResponse `[ season: {}, signups: [{}, {}, ...]]`
     */
    public function getSignupsApi()
    {
        $activeSeasons = Season::activeWithSignups()->get();
        $seasonIds = $activeSeasons->pluck('id')->toArray();
        $activeSeasons = $activeSeasons->toArray();

        $signups = Signup::whereIn('season_id', $seasonIds)
                         ->orderBy('season_id')
                         ->orderBy('created_at')
                         ->get()
                         ->toArray();

        $res = $this->groupByField('season', 'signups', $activeSeasons, $signups, 'season_id');
        return response()->json($res);
    }

    public function getSignupsBySeasonApi($season_id)
    {
        $signups = Signup::where('season_id', $season_id)
                       ->orderBy('created_at')
                       ->get()
                       ->load('user.driver')
                       ->toArray();

      // Assumes that the right constructors are set for a season
        $constructors = Season::where('id', $season_id)->get()
                      ->pluck('constructors')
                      ->toArray();
        $constructors = $constructors[0];

        $res = array();
        foreach ($signups as $signup) {
          // Assumes Car Preferences have been set with valid values
            $cars = explode(',', $signup['carprefrence']);

            for ($i = 0; $i < count($cars); $i++) {
                $cars[$i] = $constructors[array_search($cars[$i], array_column($constructors, "id"))]['game'];
            }

            $driver = $signup['user']['driver'];
            $el = [
                'id' => (is_null($driver)) ? null : $driver['id'],
                'drivername' => (is_null($driver)) ? null : $driver['name'],
                'discord_id' => $signup['user']['discord_id'],
                'racenumber' => $signup['drivernumber'],
                'steam_id' => $signup['user']['steam_id'],
                'attendance' => $signup['attendance'],
            ];

          // In-game car value
            foreach ($cars as $k => $car) {
                $el['car' . ($k + 1)] = $car;
            }

            array_push($res, $el);
        }

      // Returns [{id, drivername, discord_id, racenumber, steam_id, attendance, car1 ...}, {...} ...]
        return response()->json($res);
    }

    // Groups By Field
    // Example: Inputs -> ['season', 'signups', List of seasons, List of all signups, 'season']
    // Returns [ season: {}, signups: [{}, {}, ...] ]
    private function groupByField($fieldName, $listName, $idList, $ogList, $field, $id = "id")
    {
        $res = array();
        $sList = array();

        $prev = -1;
        $cur = 0;

        if (count($ogList) > 0) {
            $prev = $ogList[0][$field];
        }

        // Group by Field
        // Assumes ogList is sorted by field, so that it can be split by it in order
        foreach ($ogList as $l) {
            $cur = $l[$field];

            if ($prev != $cur) {
                $elId = array_search($prev, array_column($idList, $id));

                $el = array();
                $el[$fieldName] = $idList[$elId];
                $el[$listName] = $sList;
                array_push($res, $el);

                $sList = array();
            }

            array_push($sList, $l);
            $prev = $cur;
        }

        // Last element push
        $elId = array_search($cur, array_column($idList, $id));

        $el = array();
        $el[$fieldName] = $idList[$elId];
        $el[$listName] = $sList;

        array_push($res, $el);

        return $res;
    }
}
