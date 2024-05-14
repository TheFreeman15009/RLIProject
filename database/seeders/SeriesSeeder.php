<?php

// namespace Database\Seeder;

use App\Series;
use Illuminate\Database\Seeder;

// phpcs:ignore
class SeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($seriesId = null)
    {
        if ($seriesId == null) {
            $seriesId = factory(Series::class)->create()->id;
        }

        $seasonCount = mt_rand(2, 21);
        $SS = new SeasonSeeder();
        for ($i = 1; $i < $seasonCount; ++$i) {
            // $this->callWith(SeasonSeeder::class, false, [      // For Laravel 8.2+
            //     'seriesId' => $seriesId,
            //     'sn' => $sn,
            // ]);
            $SS->run($seriesId, $i);
        }
    }
}
