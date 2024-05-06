<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Series;
use App\Circuit;
use Faker\Generator as Faker;

$factory->define(Circuit::class, function (Faker $faker, $params) {
    $seriesId = (array_key_exists("series", $params)) ? $params['series'] : factory(Series::class)->create();
    $game = (array_key_exists("game", $params)) ? $params['game'] : $faker->optional()->word;

    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'series' => $seriesId,
        'title' => $faker->company,

        'name' => $faker->country . " Grand Prix",
        'official' => $faker->state,
        'laps' => $faker->randomNumber,
        'track_length' => (string)$faker->randomFloat(3) . " km",

        'location' => $faker->optional()->city,
        'country' => $faker->optional()->country,
        'display' => $faker->optional()->randomElement([
            'https://formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/China_Circuit.png.transform/9col/image.png'
        ]),
        'flag' => $faker->optional()->randomElement([
            'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Flags%2016x9/india-flag.png.transform/9col/image.png'
        ]),
        'game' => $game,
        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
