<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Season;
use App\Series;
use App\Circuit;
use App\Constructor;
use Faker\Generator as Faker;

$factory->define(Season::class, function (Faker $faker, $params) {
    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();
    $reportWindow = $faker->optional()->datetime();

    $ttracks = "";
    $constructors = "";
    $seriesId = (array_key_exists("series", $params)) ? $params['series'] : factory(Series::class)->create();

    if (array_key_exists('constructors', $params)) {
        $constructors = $params['constructors'];
    } else {
        $constructorCount = $faker->numberBetween(2, 15);
        $constructors = factory(Constructor::class)->create()->id;
        for ($i = 0; $i < $constructorCount; ++$i) {
            $constructors .= "," . factory(Constructor::class)->create()->id;
        }
    }

    if (array_key_exists('ttracks', $params)) {
        $ttracks = $params['ttracks'];
    } else {
        $tttracks = factory(Circuit::class)->create(['series' => $seriesId])->id . ',';
        $tttracks .= factory(Circuit::class)->create(['series' => $seriesId])->id . ',';
        $tttracks .= factory(Circuit::class)->create(['series' => $seriesId])->id;
    }

    return [
        'series' => $seriesId,
        'constructors' => $constructors,                        // List of comma separated Constructor IDs
        'tttracks' => $tttracks,                                // List of 3 Circuit IDs

        'show' => (int)$faker->boolean,
        'season' => $faker->randomFloat(2),
        'tier' => $faker->randomFloat(2),
        'tiername' => $faker->words(3, true),
        'year' => $faker->year,
        'status' => $faker->numberBetween(0, 1) + $faker->randomFloat(1, 0, 0.3),

        'name' => $faker->optional()->words(3, true),
        'game' => $faker->optional()->company,
        'year' => $faker->optional()->year,
        'report_channel' => $faker->optional()->regexify('[1-9][0-9]{17}'),
        'verdict_channel' => $faker->optional()->regexify('[1-9][0-9]{17}'),
        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt,
        'report_window' => ($reportWindow != null) ? $reportWindow->format('Y-m-d H:i:s') : $reportWindow
    ];
});
