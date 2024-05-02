<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Series;
use App\Discord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function viewprofile(User $user)
    {
        $discord = new Discord();
        $userroles = $discord->getroles($user->discord_id);
        $metaUserAvatar = $user->avatar;
        $metaName = $user->name;
        $series = Series::where('profile', true)->get();
        return view('user.profiles')
          ->with('user', $user)
          ->with('roles', $userroles)
          ->with('metaUserAvatar', $metaUserAvatar)
          ->with('metaName', $metaName)
          ->with('series', $series);
    }

    public function savedetails(Request $request, User $user)
    {
        $id = Auth::user()->discord_id;
        $dis = new DiscordController();
        // Dank code for auto roles

        // Save query
        $location = $request->city . "~" . $request->state;
        if (isset($request->game)) {
            $games = serialize($request->game);
            $user->games = $games;
        } else {
            $user->games = null;
        }

        if (isset($request->platform)) {
            $platformdata = serialize($request->platform);
            $user->platform = $platformdata;
        } else {
            $user->platform = null;
        }

        if (isset($request->device)) {
            $devicedata = serialize($request->device);
            $user->device = $devicedata;
        } else {
            $user->device = null;
        }

        $user->mothertongue = trim($request->mothertongue);
        $user->location = $location;
        $user->motorsport = trim($request->motorsport);
        $user->driversupport = trim($request->driversupport);
        $user->source = trim($request->source);
        $user->youtube = $request->youtube;
        $user->reddit = $request->reddit;
        $user->instagram = $request->instagram;
        $user->twitch = $request->twitch;
        $user->twitter = $request->twitter;
        $user->xbox = $request->xbox;
        $user->psn = $request->psn;
        $user->spotify = $request->spotify;
        $user->devicename = trim($request->devicename);
        $user->save();

        session()->flash('savedProfile', 'Details saved successfully.');
        $dis->applyRoles();

        return redirect()->route('user.home');
    }
}
