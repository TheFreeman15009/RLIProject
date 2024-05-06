<?php

namespace Tests\Controllers;

use App\Series;
use App\Circuit;
use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CircuitsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function testIndex()
    {
        $seriesId = factory(Series::class)->create()->id;
        $circuits = factory(Circuit::class, 2)->create([
            'series' => $seriesId
        ]);

        $this->json('GET', route('circuits.index', ['series' => $seriesId, 'fields' => 'id']))
             ->assertOk()
             ->assertJsonCount(2)
             ->assertExactJson([
                [
                    'id' => $circuits[0]['id']
                ],
                [
                    'id' => $circuits[1]['id']
                ],
             ]);

        $this->json('GET', route('circuits.index', ['series' => '999']))
             ->assertOk()
             ->assertJsonCount(0);
    }
}
