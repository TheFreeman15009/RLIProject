<?php

namespace App\Http\Middleware;

use Closure;
use App\Season;
use App\Series;
use Illuminate\Contracts\View\View;

class LoadTopBar
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $all_seasons = Season::where([
            ['status', '>=', 1],
            ['show', '=', 1]
        ])
        ->orderBy('series_id', 'asc')
        ->orderBy('tier', 'asc')
        ->orderBy('season', 'desc')
        ->get()
        ->toArray();

        $prev = -1;
        $prev = 0;
        $seasons = array();
        for ($i = 0; $i < count($all_seasons); $i++) {
            $series = array();
            while ($i < count($all_seasons) && $all_seasons[$i]['series_id'] == $all_seasons[$prev]['series_id']) {
                // if($all_seasons[$i]['season'] == (int)$all_seasons[$i]['season'])
                array_push($series, $all_seasons[$i]);

                $i++;
            }

            $prev = $i;
            $i--;
            if (count($series) > 0) {
                array_push($seasons, $series);
            }
        }

        $res = array();
        for ($i = 0; $i < count($seasons); $i++) {
            $tier = array();
            $prev = 0;
            for ($j = 0; $j < count($seasons[$i]); $j++) {
                $seq = array();
                while ($j < count($seasons[$i]) && $seasons[$i][$j]['tier'] == $seasons[$i][$prev]['tier']) {
                    array_push($seq, $seasons[$i][$j]);
                    $j++;
                }

                $prev = $j;
                $j--;
                array_push($tier, $seq);
            }

            $series_n = Series::find($tier[0][0]['series_id']);
            array_push($res, array("name" => $series_n, "tier" => $tier));
        }

        // session(['topBarSeasons' => $res]);
        view()->composer('*', function (View $view) use ($res) {
            $view->with('topBarSeasons', $res);
        });

        return $next($request);
    }
}
