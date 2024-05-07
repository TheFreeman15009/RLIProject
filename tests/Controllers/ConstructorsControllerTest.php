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
        $constructors = factory(Constructor::class, 2)->create(['series_id' => $seriesId]);

        $this->json('GET', route('constructors.index', ['series_id' => $seriesId, 'fields' => 'id']))
             ->assertOk()
             ->assertJsonCount(2)
             ->assertExactJson([
                [
                    'id' => $constructors[0]['id']
                ],
                [
                    'id' => $constructors[1]['id']
                ],
             ]);

        $this->json('GET', route('constructors.index', ['series_id' => '999']))
             ->assertOk()
             ->assertJsonCount(0);
    }
}
