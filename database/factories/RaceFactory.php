<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Race;
use App\Season;
use App\Points;
use App\Circuit;
use Faker\Generator as Faker;

$factory->define(Race::class, function (Faker $faker, $params) {
    $seasonId = (array_key_exists('season_id', $params)) ? $params['season_id'] : factory(Season::class)->create()->id;
    $pointsId = (array_key_exists('points_id', $params)) ? $params['points_id'] : factory(Points::class)->create();

    $circuitId = 0;
    if (array_key_exists('circuit_id', $params)) {
        $circuitId = $params['circuit_id'];
    } else {
        $seriesId = Season::find($seasonId)->series_id;
        $circuitId = factory(Circuit::class)->create(['series_id' => $seriesId]);
    }

    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'season_id' => $seasonId,
        'circuit_id' => $circuitId,
        'points_id' => $pointsId,

        'round' => $faker->randomNumber,
        'distance' => $faker->randomFloat(2, 0, 1),

        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
