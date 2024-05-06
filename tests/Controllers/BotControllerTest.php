<?php

namespace Tests\Controllers;

use App\User;
use App\Driver;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BotControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchUserDetailsWithSensitiveAttribute()
    {
        $user = factory(User::class)->create();
        $userToken = $user->api_token;
        $discordId = $user->discord_id;
        $response = $this->get(route('bot.discord', [
                        'query' => User::SENSITIVEATTRIBUTES[0],
                        'discord_id' => $discordId
                    ]), [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $userToken
                    ]);

        $response->assertForbidden();
    }
    public function testFetchUserDetailsWithNonExistentDiscordId()
    {
        $user = factory(User::class)->create();
        $userToken = $user->api_token;
        $response = $this->get(route('bot.discord', [
                        'query' => 'steam_id',
                        'discord_id' => '1'
                    ]), [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $userToken
                    ]);

        $response->assertNotFound();
    }
    public function testFetchUserDetails()
    {
        $user = factory(User::class)->create();
        $userToken = $user->api_token;
        $discordId = $user->discord_id;
        $response = $this->get(route('bot.discord', [
                        'query' => 'steam_id',
                        'discord_id' => $discordId,
                    ]), [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $userToken
                    ]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => $user->steam_id
        ]);
    }

    public function testFetchDriverIdExists()
    {
        $driver = factory(Driver::class)->create();
        $userToken = $driver->user->api_token;
        $discordId = $driver->user->discord_id;
        $response = $this->get(route('bot.driverid', ['discord_id' => $discordId]), [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $userToken
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $driver->id,
                'user_id' => $driver->user_id,
                'name' => $driver->name,
                'alias' => $driver->alias
            ]
        ]);
    }
    public function testFetchDriverIdNotExists()
    {
        $driver = factory(Driver::class)->create();
        $userToken = $driver->user->api_token;
        $discordId = $driver->user->discord_id;
        $response = $this->get(route('bot.driverid', ['discord_id' => '1']), [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $userToken
        ]);

        $response->assertNotFound();
    }

    public function testFetchDrivers()
    {
        $drivers = factory(Driver::class, 2)->create();
        $userToken = $drivers[0]->user->api_token;
        $response = $this->get(route('bot.drivers'), [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $userToken
        ]);

        $response->assertOk();
        $response->assertExactJson([
            [
                'id' => $drivers[0]->id,
                'user_id' => $drivers[0]->user_id,
                'user' => [
                    'id' => $drivers[0]->user_id,
                    'discord_id' => $drivers[0]->user->discord_id,
                    'name' => $drivers[0]->user->name,
                ]
            ],
            [
                'id' => $drivers[1]->id,
                'user_id' => $drivers[1]->user_id,
                'user' => [
                    'id' => $drivers[1]->user_id,
                    'discord_id' => $drivers[1]->user->discord_id,
                    'name' => $drivers[1]->user->name,
                ]
            ]
        ]);
    }
}
