<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Series;
use App\Constructor;
use Faker\Generator as Faker;

$factory->define(Constructor::class, function (Faker $faker, $params) {
    $seriesId = (array_key_exists('series_id', $params)) ? $params['series_id'] : factory(Series::class)->create();
    $game = (array_key_exists("game", $params)) ? $params['game'] : $faker->optional()->word;

    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'name' => $faker->company,

        'official' => $faker->optional()->hexcolor,
        'game' => $game,
        'logo' => $faker->optional()->randomElement([
            'https://cdn.discordapp.com/attachments/635742492192669696/939176367159910470/1.png'
        ]),
        'car' => $faker->optional()->randomElement([
            'https://www.f1gamesetup.com/img/teams/2021/ferrari-2021.png'
        ]),

        'series_id' => $seriesId,
        'title' => $faker->company,

        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
