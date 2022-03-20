<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Season;
use App\Signup;
use Faker\Generator as Faker;
use App\Http\Controllers\Controller;

$factory->define(Signup::class, function (Faker $faker) {
    $controller = new Controller();
    $carpref = factory(Season::class)->create()->id . ',';
    $carpref .= factory(Season::class)->create()->id . ',' . factory(Season::class)->create()->id;
    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'user_id' => factory(User::class)->create(),
        'season' => factory(Season::class)->create(),
        'carprefrence' => $carpref,                                                                         // 3 Constructor IDs

        'attendance' => (int)$faker->boolean,
        'speedtest' => 'https://www.speedtest.net/' . $faker->slug,
        'timetrial1' => $controller->convertMillisToStandard($faker->randomNumber),                         // Standard F1 Format time
        'timetrial2' => $controller->convertMillisToStandard($faker->randomNumber),
        'timetrial3' => $controller->convertMillisToStandard($faker->randomNumber),
        'ttevidence1' => 'timtrials/' . $faker->sha256,
        'ttevidence2' => 'timtrials/' . $faker->sha256,
        'ttevidence3' => 'timtrials/' . $faker->sha256,
        'assists' => 'a:4:{i:0;s:8:"traction";i:1;s:3:"abs";i:2;s:4:"line";i:3;s:9:"autogears";}',

        'drivernumber' => $faker->optional()->randomNumber,
        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
