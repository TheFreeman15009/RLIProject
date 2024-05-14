<?php

// namespace Database\Seeder;

use App\Series;
use Illuminate\Database\Seeder;

// phpcs:ignore
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $seriesCount = mt_rand(2, 4);
        $SS = new SeriesSeeder();
        for ($i = 1; $i < $seriesCount; ++$i) {
            $series = factory(Series::class)->create();
            echo 'Seeding Series ' . $i . "\n\n";
            // $this->callWith(SeriesSeeder::class, false, [      // For Laravel 8.2+
            //     'seriesId' => $seriesId,
            //     'sn' => $sn,
            // ]);
            $SS->run($series->id);
        }
    }
}
