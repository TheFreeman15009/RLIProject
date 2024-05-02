<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Race;
use App\Result;
use App\Driver;
use App\Constructor;
use Faker\Generator as Faker;
use App\Http\Controllers\Controller;

$factory->define(Result::class, function (Faker $faker, $params) {
    $raceId = (array_key_exists('race_id', $params)) ? $params['race_id'] : factory(Race::class)->create();
    $driverId = (array_key_exists('driver_id', $params)) ? $params['driver_id'] : factory(Driver::class)->create();
    $constructorId = (array_key_exists('constructor_id', $params)) ? $params['constructor_id'] : factory(Constructor::class)->create();
    $position = (array_key_exists('position', $params)) ? $params['position'] : $faker->randomNumber;

    $controller = new Controller();
    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'race_id' => $raceId,
        'driver_id' => $driverId,
        'constructor_id' => $constructorId,

        'position' => $position,
        'points' => 0,
        'time' => $controller->convertMillisToStandard($faker->randomNumber),
        'fastestlaptime' => $controller->convertMillisToStandard($faker->randomNumber),
        'status' => 0,

        'grid' => $faker->optional()->randomNumber,
        'stops' => $faker->optional()->randomNumber,
        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
