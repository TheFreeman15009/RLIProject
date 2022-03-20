<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Race;
use App\Result;
use App\Driver;
use App\Constructor;
use Faker\Generator as Faker;

$factory->define(Result::class, function (Faker $faker) {
    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'race_id' => factory(Race::class)->create(),
        'constructor_id' => factory(Constructor::class)->create(),
        'driver_id' => factory(Driver::class)->create(),
        'grid' => $faker->optional()->randomNumber,
        'time' => '1:19.119',
        'stops' => $faker->optional()->randomNumber,
        'points' => $faker->randomNumber,
        'status' => $faker->numberBetween(-2, 1) + $faker->randomFloat(2, 0, 0.5),
        'fastestlaptime' => '1:19.119',
        'position' => $faker->randomNumber,
        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
