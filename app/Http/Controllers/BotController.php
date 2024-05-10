<?php

namespace App\Http\Controllers;

use App\User;
use App\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class BotController extends Controller
{
    public function fetchUserDetails($query, $discord_id)
    {
        if (in_array($query, User::SENSITIVEATTRIBUTES)) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        // Check if the column requested exists in the DB
        $doesItExist = Schema::hasColumn('users', $query);
        if ($doesItExist) {
            $user = User::select("{$query}")->where('discord_id', $discord_id)->first();
            return (is_null($user)) ?
                response()->json(['message' => 'Discord ID not found'], Response::HTTP_NOT_FOUND)
                : response()->json(['data' => $user->$query]);
        } else {
            return response()->json(['message' => 'Invalid parameters', Response::HTTP_BAD_REQUEST]);
        }
    }

    public function fetchDriverId($discord_id)
    {
        $user = User::select('id')->where('discord_id', $discord_id)->first();
        if (is_null($user)) {
            return response()->json(['error' => 'Driver ID not found'], Response::HTTP_NOT_FOUND);
        } else {
            return response()->json(['data' => $user->driver->toArray()]);
        }
    }

    public function fetchDrivers()
    {
        $data = Driver::select("id", "user_id")->get()->load('user:id,discord_id,name')->toArray();
        return response()->json($data);
    }
}
