<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Input;
use Intervention\Image\Image as Img;
use App\Http\Requests\RaceResults;
use App\Driver;
use App\Circuit;
use App\Constructor;
use App\User;
use App\Season;
use App\Race;
use App\Result;

class ImageController extends Controller
{
    private $output;
    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    public function raceIndex()
    {
        $seasons = Season::active()->get();
        return view('raceimage')->with('seasons', $seasons);
    }
    public function qualiIndex()
    {
        return view('qualiimage');
    }

    protected function thicken(Img $img)
    {
        $black_arr = array();
        for ($i = 0; $i < $img->width(); $i++) {
            for ($j = 0; $j < $img->height(); $j++) {
                $colorA = $img->pickColor($i, $j, 'hex');
                if ($colorA == '#000000') {
                    array_push($black_arr, array($i, $j));
                }
            }
        }

        $s = count($black_arr);
        for ($k = 0; $k < $s; $k++) {
            $img->pixel('#000000', $black_arr[$k][0] - 1, $black_arr[$k][1] - 1);
            $img->pixel('#000000', $black_arr[$k][0], $black_arr[$k][1] - 1);
            $img->pixel('#000000', $black_arr[$k][0] + 1, $black_arr[$k][1] - 1);
            $img->pixel('#000000', $black_arr[$k][0] - 1, $black_arr[$k][1]);
            $img->pixel('#000000', $black_arr[$k][0] + 1, $black_arr[$k][1]);
            $img->pixel('#000000', $black_arr[$k][0] - 1, $black_arr[$k][1] + 1);
            $img->pixel('#000000', $black_arr[$k][0], $black_arr[$k][1] + 1);
            $img->pixel('#000000', $black_arr[$k][0] + 1, $black_arr[$k][1] + 1);
        }
        return 0;
    }
    protected function twoTone(Img $img)
    {
        $thres = 86;
        $white = 0;
        $black = 0;
        for ($i = 0; $i < $img->width(); $i++) {
            for ($j = 0; $j < $img->height(); $j++) {
                $colorA = $img->pickColor($i, $j);
                if ($colorA[0] > $thres && $colorA[1] > $thres && $colorA[2] > $thres) {
                // if($color != 4287993237) {
                    $img->pixel('#000000', $i, $j);
                    $black++;
                } else {
                    $img->pixel('#ffffff', $i, $j);
                    $white++;
                }
            }
        }

        if ($black > $white) {
            $img->invert();
        }

        // $this->thicken($img);
        $img->save('storage/img/race_results/' . microtime() . '.png');
        return $img;
    }

    protected function getImage(string $src, string $sec, int $pos = 0)
    {

        // Name
        if ($sec == "name") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1000, 90, 90, 215);

            $this->twoTone($img);
            return $img;
        }


        /*// Standings
        $img = Image::make($src)
             ->resize(1920, 1080)
             ->crop(1290, 570, 530, 360)
             ->save('storage/img/race_results/Standings.png');

        $img = Image::make('storage/img/race_results/Standings.png')
             ->crop(150, 33, 150, 7)
             ->save('storage/img/race_results/SD.png');*/

        // $img->crop(1, 10, 5, 9);
        // $img->save('storage/img/race_results/SDI.png');
        //    $this->twoTone($img);
        //  $img->save('storage/img/race_results/SDI.png');

        $row_width = 40.2142;

        // Position
        if ($sec == "pos") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(50, 33, 10, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Driver
        if ($sec == "driver") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(200, 33, 150, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Team
        if ($sec == "team") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(240, 33, 555, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Grid
        if ($sec == "grid") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(50, 33, 821, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Stops
        if ($sec == "stops") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(50, 33, 910, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Fastest Lap
        if ($sec == "best") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(150, 33, 1000, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Finishing Time
        if ($sec == "time") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(140, 33, 1140, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }
        return 0;
    }

    protected function getImage2018(string $src, string $sec, int $pos = 0)
    {

        // Name
        if ($sec == "name") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1000, 90, 90, 215);

            $this->twoTone($img);
            return $img;
        }


        /*// Standings
        $img = Image::make($src)
             ->resize(1920, 1080)
             ->crop(1290, 570, 530, 360)
             ->save('storage/img/race_results/Standings.png');

        $img = Image::make('storage/img/race_results/Standings.png')
             ->crop(150, 33, 150, 7)
             ->save('storage/img/race_results/SD.png');*/

        // $img->crop(1, 10, 5, 9);
        // $img->save('storage/img/race_results/SDI.png');
        //    $this->twoTone($img);
        //  $img->save('storage/img/race_results/SDI.png');

        $row_width = 40.2142;

        // Position
        if ($sec == "pos") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(50, 33, 10, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Driver
        if ($sec == "driver") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(200, 33, 117, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Team
        if ($sec == "team") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(275, 33, 495, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Grid
        if ($sec == "grid") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(40, 33, 768, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Stops
        if ($sec == "stops") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(35, 33, 855, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Fastest Lap
        if ($sec == "best") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(100, 33, 960, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }

        // Finishing Time
        if ($sec == "time") {
            $img = Image::make($src)
                        ->resize(1920, 1080)
                        ->crop(1290, 570, 530, 360)
                        ->crop(120, 33, 1080, 7 + (int)($pos * $row_width));

            $this->twoTone($img);
            return $img;
        }
        return 0;
    }

    protected function closestMatch($input, $dic)
    {

        // no shortest distance found, yet
        $shortest = -1;
        $index = 0;

        // loop through words to find the closest
        foreach ($dic as $i => $word) {
            // calculate the distance between the input word,
            // and the current word
            $lev = levenshtein($input, $word);

            // check for an exact match
            if ($lev == 0) {
                // closest word is this one (exact match)
                // $closest = $word;
                $index = $i;
                $shortest = 0;

                // break out of the loop; we've found an exact match
                break;
            }

            // if this distance is less than the next found shortest
            // distance, OR if a next shortest word has not yet been found
            if ($lev <= $shortest || $shortest < 0) {
                // set the closest match, and shortest distance
                // $closest  = $word;
                $index = $i;
                $shortest = $lev;
            }
        }

        $res = array($index, $shortest);
        return $res;
    }

    protected function crudeFlatten($array)
    {
        $res = array();
        for ($i = 0; $i < count($array); $i++) {
            $ires = array();
            $ires['id'] = $array[$i]['id'];
            $ires['name'] = $array[$i]['name'];

            foreach ($array[$i]['alias'] as $j => $alias) {
                $ires['alias'] = $alias;
                array_push($res, $ires);
            }
        }
        return $res;
    }

    public function raceprep(string $src, bool $pub = false, $seasonid = null)
    {
        $tess = new TesseractOCR();
        $tess->lang('eng')->psm(7);

        $drivers = Driver::getNames();
        $constructors = Constructor::getTeams();
        $flat_drivers = $this->crudeFlatten((array)$drivers);

        $results = array();
        for ($i = 0; $i < 14; $i++) {
            $row = array();
            $this->output->writeln("<info>Driver " . ($i + 1) . " : " . "</info>");

            // Position
            $img = $this->getImage($src, "pos", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            // If No More Results
            if ($tr == "-") {
                break;
            }

            $row["position"] = (int)$tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            // Driver
            $img = $this->getImage($src, "driver", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            $row["driver"] = $tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            if (!$pub) {
                $name = array_column($drivers, 'name', $i);
                $index = $this->closestMatch($tr, $name);
                $used = true;
                if ($index[1] != 0) {
                    $fname = array_column($flat_drivers, 'alias');
                    $findex = $this->closestMatch($tr, $fname);

                    if ($findex[1] < $index[1]) {
                        $row["driver_id"] = $flat_drivers[$findex[0]]['id'];
                        $row["matched_driver"] = $flat_drivers[$findex[0]]['alias'];
                        $used = false;
                    }
                }

                if ($used) {
                    $row["driver_id"] = $drivers[$index[0]]['id'];
                    $row["matched_driver"] = $drivers[$index[0]]['name'];
                }
            }

            // Team
            $img = $this->getImage($src, "team", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            $row["team"] = $tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            if (!$pub) {
                $name = array_column($constructors, 'name');
                $index = $this->closestMatch($tr, $name);
                $row["constructor_id"] = $constructors[$index[0]]['id'];
                $row["matched_team"] = $constructors[$index[0]]['name'];
            }

            // Grid
            $img = $this->getImage($src, "grid", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            $row["grid"] = (int)$tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            // Stops
            $img = $this->getImage($src, "stops", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            $row["stops"] = (int)$tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            // Best
            $img = $this->getImage($src, "best", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            $row["fastestlaptime"] = $tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            // Time
            $img = $this->getImage($src, "time", $i);
            $tess->image($img->dirname . '/' . $img->basename);
            $tr = $tess->run();
            unlink($img->dirname . '/' . $img->basename);

            $row["time"] = $tr;
            $this->output->writeln("<info>" . $tr . "</info>");

            array_push($results, $row);
        }

        // $tess->image('storage/img/race_results/Standings.png');
        return $results; // $tess->response('png');
    }

    public function raceName(string $src, bool $pub = false)
    {
        $tess = new TesseractOCR();
        $tess->lang('eng')->psm(1);

        $imag = $this->getImage($src, "name");
        $tess->image($imag->dirname . '/' . $imag->basename);
        $tr = $tess->run();
        unlink($imag->dirname . '/' . $imag->basename);

        // Replace Series of '\n' with a single '$'
        $tri = preg_replace('/\n+/', '$', $tr);
        $track = explode("$", $tri);

        if (count($track) < 2) {
            return response()->json($track);
        }

        $circuits = Circuit::getOfficial();

        if ($pub) {
            return array(
                'official' => $track[1],
                'display' => $track[0]
            );
        }

        $official = array_column($circuits, 'official');
        $index = $this->closestMatch($track[1], $official);
        return array(
            'circuit_id' => $circuits[$index[0]]['id'],
            'official' => $track[1],
            'display' => $track[0]
        );
    }

    public function ocrRace(Request $request)
    {
        ini_set('max_execution_time', 300);
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $track = $this->raceName($request->photo->path(), false);
        $results = $this->raceprep($request->photo->path(), false, request()->season);

        $track['season_id'] = request()->season;
        $track['round'] = (int)request()->round;
        return response()->json([
            "track" => $track,
            "results" => $results
        ]);
    }
    public function pubRace(Request $request)
    {
        ini_set('max_execution_time', 300);
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $track = $this->raceName($request->photo->path(), true);
        $results = $this->raceprep($request->photo->path(), true);

        return response()->json([
            "track" => $track,
            "results" => $results
        ]);
    }
}
