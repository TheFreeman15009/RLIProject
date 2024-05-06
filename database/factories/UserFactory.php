<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker, $params) {
    $steamId = (array_key_exists("steam_id", $params)) ? $params['steam_id'] : $faker->unique()->regexify('[1-9][0-9]{16}');

    $createdAt = $faker->optional()->datetime();
    $updatedAt = $faker->optional()->datetime();

    return [
        'email' => $faker->unique()->safeEmail,
        'discord_id' => $faker->unique()->regexify('[1-9][0-9]{17}'),
        'steam_id' => $steamId,
        'api_token' => $faker->unique()->regexify('[1-9][0-9]{16,24}'),

        'name' => $faker->name,
        'role_id' => $faker->numberBetween(1, 3),
        'discord_discrim' => $faker->numerify('####'),
        'remember_token' => $faker->regexify('[a-zA-Z0-9]{60}'),
        'isadmin' => (int)$faker->boolean(1),
        'location' => $faker->city . "~" . $faker->state,
        'mothertongue' => $faker->languageCode,
        'motorsport' => $faker->company,
        'driversupport' => $faker->name,
        'source' => $faker->sentence,

        'platform' => 'a:1:{i:0;s:2:"PC";}',
        'device' => 'a:2:{i:0;s:10:"Controller";i:1;s:5:"Wheel";}',
        'games' => 'a:4:{i:0;s:2:"f1";i:1;s:2:"ac";i:2;s:3:"acc";i:3;s:2:"pc";}',

        'avatar' => $faker->optional()->randomElement([
            'https://cdn.discordapp.com/avatars/251955629026050049/bda441e5c61614d815d6d3800760194d.jpg'
        ]),
        'devicename' => $faker->optional()->word,
        'instagram' => $faker->optional()->randomElement(['https://www.instagram.com/' . $faker->slug]),
        'twitter' => $faker->optional()->randomElement([' https://twitter.com/' . $faker->slug]),
        'twitch' => $faker->optional()->randomElement(['https://www.twitch.tv/' . $faker->slug]),
        'youtube' => $faker->optional()->randomElement(['https://www.youtube.com/' . $faker->slug]),
        'xbox' => $faker->optional()->userName,
        'psn' => $faker->optional()->userName,
        'reddit' => $faker->optional()->randomElement(['https://www.reddit.com/user/' . $faker->slug]),
        'created_at' => ($createdAt != null) ? $createdAt->format('Y-m-d H:i:s') : $createdAt,
        'updated_at' => ($updatedAt != null) ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt
    ];
});
