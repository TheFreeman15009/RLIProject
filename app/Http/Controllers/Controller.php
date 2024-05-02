<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function convertMillisToStandard(int $time): string
    {
        $mtime = (int)abs($time);
        $seconds = round($mtime / 1000.0, 3);
        $minutes = (int)($seconds / 60);
        $seconds = round($seconds - $minutes * 60, 3);

        $res = ($time < 0) ? '-' : '';
        if ($minutes > 0) {
            $res .= (string)$minutes . ":";

            if ($seconds < 10) {
                $res .= "0";
            }
        }


        $res .= (string)$seconds;
        $tr = explode(".", $res);
        if (count($tr) > 1) {
            $decimal = $tr[1];
            if (strlen($decimal) != 3) {
                for ($i = 3; $i > strlen($decimal); --$i) {
                    $res .= "0";
                }
            }
        } else {
            $res .= ".000";
        }

        return $res;
    }

    public function sgnp(float $n): int
    {
        return ($n >= 0) - ($n < 0);
    }

    public function convertStandardtoMillis(string $time): int
    {
        $sign = 1;
        if ($time[0] == '-') {
            $sign = -1;
            $time[0] = '0';
        }

        $seg_time = explode(":", $time);
        $min = 0;
        $sec = 0;
        if (count($seg_time) > 1) {
            $min = (int)$seg_time[0];
        }
        if (count($seg_time) > 1) {
            $sec = (float)$seg_time[1];
        } else {
            $sec = (float)$seg_time[0];
        }

        $res = $min * 60 + $sec;
        return $sign * ceil($res * 1000);
    }

    /**
     * Modifies input array, sorting by a key in associative array.
     *
     * @param array[array] $arr Input Array of associative arrays, which should contain $field as a key
     * @param string $field Sort key
     * @param 1|-1 $mul Sorting order. -1 for descending order.
     */
    public function sortByKey(array &$arr, string $field, int $mul = 1): void
    {
        if (count($arr) == 0 || !array_key_exists($field, $arr[0]))
            return;

        usort($arr, function ($a, $b) use ($field, $mul) {
            if ($a[$field] * $mul > $b[$field] * $mul) {
                return 1;
            } elseif ($a[$field] * $mul < $b[$field] * $mul) {
                return -1;
            } else {
                return 0;
            }
        });
    }
}
