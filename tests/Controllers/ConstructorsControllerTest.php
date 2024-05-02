<?php

namespace Tests\Controllers;

use App\Series;
use Tests\TestCase;
use App\Constructor;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ConstructorsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function testIndex()
    {
        $seriesId = factory(Series::class)->create()->id;
        $constructors = factory(Constructor::class, 2)->create([
            'series' => $seriesId
        ]);

        // Mock API authentication
        // $user = factory(User::class)->create(['id' => 2]);
        // Auth::shouldReceive('guard')->once()->with('api')->andReturnSelf();
        // Auth::shouldReceive('user')->once()->andReturn($user);

        $this->json('GET', 'api/constructors?series=' . $seriesId . '&fields=id')
             ->assertStatus(Response::HTTP_OK)
             ->assertJsonCount(2)
             ->assertExactJson([
                [
                    'id' => $constructors[0]['id']
                ],
                [
                    'id' => $constructors[1]['id']
                ],
             ]);

        $this->json('GET', 'api/constructors?series=999')
             ->assertStatus(Response::HTTP_OK)
             ->assertJsonCount(0);
    }
}
