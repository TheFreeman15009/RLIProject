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
  public function index()
  {
    return view('admin.adminhome');
  }

  public function viewusers()
  {
    return view('admin.viewusers')->with('user', User::all());
  }
  public function viewdetails(User $user)
  {
    return view('admin.viewdetails')->with('user', $user);
  }

  public function viewedit(User $user)
  {
    return view('admin.edit')->with('user', $user);
  }

  public function saveedit(User $user)
  {
    $data = request()->all();
    $user->name = $data['name'];
    $user->discord_discrim = $data['discord_discrim'];
    $user->team = $data['team'];
    $user->steam_id = $data['steam_id'];
    $user->avatar = $data['avatar'];
    $user->save();
    return redirect()->back();
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

  public function saveallotment()
  {
    $data = request()->all();
    $userinfo = User::select('*')
      ->where('id', $data['user_id'])
      ->get()->toArray();

    $driver = new Driver();
    $driver->user_id = $data['user_id'];
    $driver->name = $userinfo['0']['name'];
    $driver->drivernumber = 5;
    $driver->retired = 0;
    $driver->alias = $userinfo['0']['name'];
    $driver->tier = $data['tier'];
    $driver->save();

    return redirect()->back();
  }

  public function driverdata()
  {
    $driver = Driver::select('id', 'name', 'tier', 'team', 'drivernumber', 'user_id')
      ->get()->load('user:id,name,avatar,steam_id,xbox')->toArray();

    $constructor = Constructor::all()->toArray();

    $seasons = Season::where('status', '<', 2)->get()->toArray();
    $ts = array();

    // Iterate through all Active Seasons
    for ($i = 0; $i < count($seasons); ++$i) {
      for ($j = 0; $j < count($driver); ++$j)
        $driver[$j][$seasons[$i]['id']] = 0;
      for ($j = 0; $j < count($constructor); ++$j)
        $constructor[$j][$seasons[$i]['id']] = 0;

      // Results for this Season
      $ts = $this->computeStandings($seasons[$i]['series'], $seasons[$i]['tier'], $seasons[$i]['season']);
      if ($ts['code'] != 200)
        continue;

      // Add Points to Drivers
      for ($j = 0; $j < count($ts['drivers']); ++$j) {
        $d_id = array_search($ts['drivers'][$j]['id'], array_column($driver, "id"));
        $driver[$d_id][$seasons[$i]['id']] = $ts['drivers'][$j]['points'];
      }

      // Add Points to Constructors
      for ($j = 0; $j < count($ts['constructors']); ++$j) {
        $c_id = array_search($ts['constructors'][$j]['id'], array_column($constructor, "id"));
        $constructor[$c_id][$seasons[$i]['id']] = $ts['constructors'][$j]['points'];
      }
    }

    return response()->json([
      "drivers" => $driver,
      "constructors" => $constructor
    ]);
  }
}
