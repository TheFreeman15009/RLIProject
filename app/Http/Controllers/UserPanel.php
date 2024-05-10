<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Series;
use App\Discord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserPanel extends Controller
{
    public function viewprofile()
    {
        $id = Auth::user()->discord_id;
        $series = Series::where('profile', true)->get();
        $discord = new Discord();
        $userroles = $discord->getroles($id);

        $platform = isset(Auth::user()->platform) ? unserialize(Auth::user()->platform) : null;
        $accounts = -1;

        if (!is_null($platform)) {
            $accounts = in_array("PC", $platform) & !isset(Auth::user()->steam_id);
            $accounts <<= 1;

            $accounts |= in_array("PlayStation", $platform) & !isset(Auth::user()->psn);
            $accounts <<= 1;

            $accounts |= in_array("Xbox", $platform) & !isset(Auth::user()->xbox);
        }

        return view('user.userprofile')
                ->with('roles', $userroles)
                ->with('series', $series)
                ->with('accounts', $accounts);
    }

    public function setSteam(user $user)
    {
        $data = request()->all();
        $user->steam_id = $data['steamid'];
        $user->save();
        return redirect('/user/profile/' . $user->id);
    }

    public function resetSteamLink(user $user)
    {
        $user->update(['steam_id' => null]);
        $user->save();
    }
}
