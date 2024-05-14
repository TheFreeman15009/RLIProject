<?php

use App\Series;
use Illuminate\Database\Seeder;

// phpcs:ignore
class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($seriesId = null, $sn = null)
    {
        if ($seriesId == null) {
            $seriesId = factory(Series::class)->create()->id;
        }
        if ($sn == null) {
            $sn = mt_rand(1, 100);
        }

        $tierCount = mt_rand(2, 6);
        $TS = new TierSeeder();
        for ($i = 1; $i < $tierCount; ++$i) {
            echo 'Seeding Season ' . $sn . ', Tier ' . $i . "\n";
            // $this->callWith(TierSeeder::class, false, [      // For Laravel 8.2+
            //     'seriesId' => $seriesId,
            //     'sn' => $sn,
            //     'tier' => $i
            // ]);
            $TS->run($seriesId, $sn, $i);
        }
    }
}
