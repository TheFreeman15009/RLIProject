<?php

use App\Race;
use App\Season;
use App\Series;
use App\Signup;
use App\Driver;
use App\Result;
use App\Report;
use Illuminate\Database\Seeder;

// phpcs:ignore
class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($seriesId = null, $sn = null, $tier = null)
    {
        // 1 series
        if ($seriesId == null) {
            $seriesId = factory(Series::class)->create()->id;
        }

        // pass series_id -> 1 season -> 1 - 15 constructors (note), (pass series_id) 3 circuits
        $seasonParams = [
            'series_id' => $seriesId,
            'show' => 1
        ];
        if ($sn != null) {
            $seasonParams['season_id'] = $sn;
        }
        if ($tier != null) {
            $seasonParams['tier'] = $tier;
        }

        $season = factory(Season::class)->create($seasonParams);

        // 30 signups -> 30 users
        $signups = array();
        for ($i = 0; $i < 30; ++$i) {
            $carprefInd = array_rand($season->constructors, 3);
            $carpref = $season->constructors[$carprefInd[0]]['id'] . ',';
            $carpref .= $season->constructors[$carprefInd[1]]['id'] . ',' . $season->constructors[$carprefInd[2]]['id'];

            $signups[] = factory(Signup::class)->create([
                'season_id' => $season->id,
                'carprefrence' => $carpref
            ]);
        }
        echo "Signups Count -> " . count($signups) . "\n";

        // pass user_ids -> 22 drivers (note) -> 30 users
        $drivers = array();
        $driverCount = mt_rand(15, 27);
        for ($i = 0; $i < $driverCount; ++$i) {
            $drivers[] = factory(Driver::class)->create(['user_id' => $signups[$i]['user_id']]);
        }
        echo "Drivers Count -> " . count($drivers) . "\n";

        // pass season_id -> 12 races -> (pass series_id) 12 circuits -> 12 points systems
        $races = array();
        $raceCount = mt_rand(8, 16);
        for ($i = 0; $i < $raceCount; ++$i) {
            $races[] = factory(Race::class)->create([
                'season_id' => $season->id,
                'round' => $i + 1
            ]);
        }
        echo "Races Count -> " . count($races) . "\n\n";

        // pass race_ids, constructor_ids, driver_ids -> 15 - 22 results
        for ($i = 0; $i < $raceCount; ++$i) {
            $resultCount = mt_rand($driverCount - 5, $driverCount);
            $dnfCount = mt_rand($driverCount - 3, $driverCount - 1);
            echo "Results Count -> " . $resultCount . " for Race " . ($i + 1) . "\n";

            for ($j = 0; $j < $resultCount; ++$j) {
                $status = 0;
                if ($j == 0) {
                    $status = 1;
                } elseif ($j >= $dnfCount) {
                    $status = -2;
                }

                factory(Result::class)->create([
                    'race_id' => $races[$i]->id,
                    'driver_id' => $drivers[$j]->id,
                    'constructor_id' => $season->constructors[$j % count($season->constructors)]["id"],
                    'position' => $j + 1,
                    'status' => $status
                ]);
            }
        }

        // pass race_ids, driver_ids -> 0 - 7 reports
        for ($i = 0; $i < $raceCount; ++$i) {
            $reportCount = mt_rand(0, 7);
            for ($j = 0; $j < $reportCount; ++$j) {
                factory(Report::class)->create([
                    'race_id' => $races[$i]->id,
                    'reporting_driver' => $drivers[$j]->id,
                    'reported_against' => $drivers[mt_rand(0, 7)]->id
                ]);
            }
        }

        // Season has multiple signups
        // Signups become drivers
        // Season has many Races
        // Season has a list of Constructors
        // Each Race has result
        echo '/' . $season->tier . '/' . $season->season . '/standings' . "\n\n";
    }
}
