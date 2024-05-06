<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Race;
use App\Season;
use App\Report;
use App\Driver;
use App\Result;
use App\Discord;

class ReportsController extends Controller
{
    public function reportDriver()
    {
        // Check whether a Driver
        $dr = Driver::where('user_id', Auth::user()->id)->first();
        if ($dr == null) {
            session()->flash('error', "Need to get a license first child, Please contact a coordinator");
            return redirect('/');
        }

        // Return All Active Seasons which is Reportable
        $seasons = Season::active()
                         ->whereNotNull('report_window')
                         ->get()->toArray();

        // Return Seasons with Report Windows still open
        $seasonlist = array();
        for ($k = 0; $k < count($seasons); $k++) {
            if (time() > strtotime($seasons[$k]['report_window'])) {
                continue;
            }

            array_push($seasonlist, $seasons[$k]['id']);
        }

        // Get Max Round of Seasons, i.e. Latest Races
        $race_ids = Race::wherein('season_id', $seasonlist)
                        ->selectRaw('max(round) as round, season_id')
                        ->groupBy('season_id')
                        ->get()->toArray();

        // Get Latest Races
        $races = [];
        if (count($race_ids) > 0) {
            // [round AND season_id] OR [round AND season_id] OR ...
            $races = Race::where(function ($query) use ($race_ids) {
                for ($j = 0; $j < count($race_ids); $j++) {
                    $query->orWhere(function ($orq) use ($j, $race_ids) {
                        $orq->where('round', $race_ids[$j]['round'])
                            ->where('season_id', $race_ids[$j]['season_id']);
                    });
                }
            });

            $races = $races->get()->load(['circuit','season'])->toArray();
        }

        return view('user.createreport')->with('data', $races);
    }

    public function driversdata($race)
    {
        $result = Result::where('race_id', $race)
                        ->get()->load('driver')
                        ->pluck('driver')->toArray();

        return response()->json($result);
    }

    public function bulkCreate()
    {
        $data = request()->all();
        for ($i = 0; $i < count($data); $i++) {
            $rp = new Report($data[$i]);
            $rp->save();
        }

        return response()->json(['message' => "Reports Created Successfully"], 200);
    }

    public function create()
    {
        $data = request()->all();
        // array_push($data['driver'],'1');
        // Uncomment above line to test reports with multiple drivers being reported
        // Will remove this after frontend is done

        $dr = Driver::where('user_id', Auth::user()->id)->firstOrFail();  // UI Version
        // $dr = Driver::where('user_id', $data['reporting_driver'])->firstOrFail();    // API Version
        $race = Race::where('id', $data['race'])->firstOrFail()->load('season')->toArray();

        for ($i = 0; $i < count($data['driver']); $i++) {
            $report = new Report();
            $report -> race_id = $data['race'];
            $report -> reporting_driver = $dr->id;
            $report -> reported_against = $data['driver'][$i];
            $report -> lap = $data['lap'];
            $report -> explanation = $data['explanation'];
            $report -> proof = $data['proof'];
            $report -> report_game = $data['report_game'];


            // Publish Message in Season's Report Channel
            $userid = Driver::where('id', $data['driver'][$i])->get()->load('user')->toArray();
            $report -> message_id = Discord::publishMessage(
                $this->reportMessage(
                    Auth::user()->discord_id,
                    $userid[0]['user']['discord_id'],
                    $data
                ),
                $race['season']['report_channel']
            ); // UI Version

            /* $report -> message_id = Discord::publishMessage(
                $this->reportMessage(
                    '240431392834453505',
                    $userid[0]['user']['discord_id'],
                    $data
                ),
                $race['season']['report_channel']
            );*/

            $report->save();
        }

        // Redirect to View all Reports page
        session()->flash('success', "Report Submitted Successfully"); // UI Version
        return redirect()->back();
        // return response()->json(['message'=>"Report Created Successfully"], 200);
    }

    public function applyReports()
    {
        $seasons = Season::active()->get()->toArray();
        return view('user.reportadmin')->with('seasons', $seasons);
    }

    public function publishReports()
    {
        // Requires Season
        $racelist = Race::where('season_id', request()->season)->pluck('id')->toArray();
        $season = Season::where('id', request()->season)->firstOrFail();

        if (count($racelist) == 0) {
            return abort(404);          // Actually should post 400 Error
        }

        // Taking in season instead of Race, in case of Backlog
        $reports = Report::wherein('race_id', $racelist)
                         ->where('resolved', 2)
                         ->orderBy('race_id', 'asc')
                         ->orderBy('lap', 'asc')
                         ->get()
                         ->load(['reporting_driver.user', 'reported_against.user', 'race.season']);

        $prev_race_id = -1;
        for ($i = 0; $i < count($reports); $i++) {
            // Apply Verdict
                // Find Result
                // Update Result status
                // Update Result Time (many conditions)
            // Assume Standard Format Race
            $this->applyVerdict($reports[$i]);

            // Update Resolved to 3
            $reports[$i]->resolved = 3;
            $reports[$i]->save();

            // Publish Splitter Message
            if ($prev_race_id != $reports[$i]->race_id) {
                $splitter_message = " **-----------------------------**\n       Round ";
                $splitter_message .= $reports[$i]->race->round;
                $splitter_message .= " Reports\n **-----------------------------**";
                Discord::publishMessage($splitter_message, $season->verdict_channel);
            }

            sleep(1);
            // Publish Verdict Message
            Discord::publishMessage($this->verdictMessage($reports[$i]->toArray()), $season->verdict_channel);

            $prev_race_id = $reports[$i]->race_id;
        }

        session()->flash('success', "Verdicts Applied Successfully");
        return redirect('/');
    }

    public function revertVerdict(Report $report)
    {
        if ($report->resolved == 3) {
            $this->applyVerdict($report, -1);

            // Delete Verdict Message
            $report->loadMissing('race.season');
            Discord::deleteMessage($race->race->season->report_channel, $report->message_id);

            // Update Resolved to 1
            $report->resolved = 1;
            $report->save();

            session()->flash('success', "Verdict Reverted Successfully");
            return redirect('/');           // Should revert to previous page.
        } else {
            session()->flash('error', "Verdict is not Applied");
            return redirect('/');           // Should revert to previous page.
        }
    }

    private function reportMessage($idfor, $idagainst, $data)
    {
        $message = "1. Reporting Driver: <@" . $idfor . "> \n2. Against: <@";
        $message .= $idagainst . "> \n3. ";

        // Lap
        if ($data['lap'] == -1) {
            $message .= "In Quali";
        } elseif ($data['lap'] == 0) {
            $message .= "Formation Lap";
        } else {
            $message .= "Lap " . $data['lap'];
        }

        $message .= "\n4. Explanation: " . $data['explanation'];
        $message .= "\n5. Proof: " . $data['proof'];
        $message .= "\n-----------------------------";

        return $message;
    }

    // Assume loaded reporting_driver, reported_against, race.season
    private function verdictMessage(array $report)
    {
        // Driver
        $message = "1. Driver: <@" . $report['reported_against']['user']['discord_id'] . "> \n2. ";

        // Lap
        if ($report['lap'] == -1) {
            $message .= "In Quali";
        } elseif ($report['lap'] == 0) {
            $message .= "Formation Lap";
        } else {
            $message .= "Lap " . $report['lap'];
        }

        $message .= "\n3. Verdict: **";

        // Verdict Time
        if ($report['verdict_time'] == 0 && $report['verdict_pp'] == 0) {
            $message .= "NFA";
        }
        if ($report['verdict_time'] > 0) {
            $message .= abs($report['verdict_time']) . " seconds Time Penalty";
        } elseif ($report['verdict_time'] < 0) {
            $message .= abs($report['verdict_time']) . " seconds Removed";
        }

        // Verdict PP
        if ($report['verdict_time'] != 0 && $report['verdict_pp'] != 0) {
            $message .= " + ";
        }
        if ($report['verdict_pp'] != 0) {
            $penalties = round((abs($report['verdict_pp']) - (int)abs($report['verdict_pp'])) * 10, 2);

            // Penalty Points
            if ((int)$penalties != 0) {
                $message .= (int)$penalties . " Penalty Point";
            }
            if ((int)abs($penalties) > 1) {
                $message .= "s";
            }

            // Warning
            if ((int)$penalties != 0 && $penalties != (int)$penalties) {
                $message .= " + Warning";
            } elseif ($penalties != (int)$penalties) {
                $message .= "Warning";
            }
        }

        $message .= "**\n4. Evidence: " . $report['proof'];


        // Explanation
        if ($report['verdict_message'] != null) {
            $message .= "\n5. Explanation: " . $report['verdict_message'];
        }

        $message .= "\n-----------------------------";
        return $message;
    }

    public function update()   // Check this function once the frontend page is done
    {
        $report = Report::findOrFail(request()->report)
                        ->load(['reporting_driver', 'race.season']);

        $rA = $report->toArray();

        // Checks for Update:
        // 1. Check if Reporting Driver is the Authenticated User
        // 2. Check if Report is already Resolved/Published by Stewards
        // 3. Check if Report Update is in Reporting Window
        if (
            $rA['reporting_driver']['user_id'] != Auth::user()->id ||
            $rA['resolved'] > 0 ||
            !($rA['race']['season']['report_window'] != null &&
            time() < strtotime($rA['race']['season']['report_window']))
        ) {
            session()->flash('error', "You are not allowed to Update this report");
            return redirect('/');
        }

        $data = request()->all();
        $report -> reported_against = $data['driver'];
        $report -> lap = $data['lap'];
        $report -> explanation = $data['explanation'];
        $report -> proof = $data['proof'];
        $report->save();

        // Update Posted Message
        $userid = Driver::where('id', $data['driver'])->firstOrFail()->load('user');
        Discord::editMessage(
            $this->reportMessage(
                Auth::user()->discord_id,
                $userid['user']['discord_id'],
                $data
            ),
            $rA['race']['season']['report_channel'],
            $rA['message_id']
        );

        session()->flash('success', "Report updated successfully");
        return redirect('/');
    }

    public function listDriverReports()
    {
        // Search for driver, if not, respond "Need to get a license first child"
        $driver = Driver::where('user_id', Auth::user()->id)->firstOrFail();
        $reports = Report::where('reporting_driver', $driver['id'])
                         ->orWhere('reported_against', $driver['id'])
                         ->orderBy('created_at', 'desc')
                         ->get()
                         ->load(['reporting_driver', 'reported_against', 'race.season', 'race.circuit']);

        return view('user.viewreports')->with('reports', $reports);
    }

    public function details()
    {
        $report = Report::findOrFail(request()->report)
                        ->load(['reporting_driver', 'reported_against', 'race.season', 'race.circuit'])->toArray();

        if (
            !($report['reporting_driver']['user_id'] == Auth::user()->id ||
            $report['reported_against']['user_id'] == Auth::user()->id)
        ) {
            session()->flash('error', "You are not allowed to view this report");
            return redirect()->route('report.list');
        }

        return view('user.reportdetails')->with('report', $report);
    }

    public function delete()
    {
        $report = Report::findOrFail(request()->report)
                        ->load(['reporting_driver', 'reported_against']);

        // Checks for Update:
        // 1. Check if Reporting Driver is the Authenticated User
        // 2. Check if Report is already Resolved/Published by Stewards
        // 3. Check if Report Update is in Reporting Window
        if (
            $rA['reporting_driver']['user_id'] != Auth::user()->id ||
            $rA['resolved'] > 0 ||
            !($rA['race']['season']['report_window'] != null &&
            time() < strtotime($rA['race']['season']['report_window']))
        ) {
            session()->flash('error', "You are not allowed to delete this report");
            return redirect('/');
        }


        // Delete Posted Message
        Discord::deleteMessage($rA['season']['report_channel'], $rA['message_id']);
        $report->delete();

        session()->flash('success', "Report deleted successfully");
        return redirect('/');
    }

    public function applyVerdict(Report $report, $mul = 1)
    {
        $report->loadMissing('race.season');

        // Assume fully loaded Report
        $result = Result::where('race_id', $report->race->id)
                             ->where('driver_id', $report->reported_against)
                             ->firstOrFail();

        $report->verdict_time *= $mul;
        $report->verdict_pp *= $mul;

        $dnfpattern = "/^DNF$|^DSQ$|^\+1 Lap$|^\+[2-9][0-9]* Laps$|^-$/";
        $updatedPos = $result->position;

        if ($result->position == 1 && $report->verdict_time < 0) {
            // 1. If verdict_time < 0 AND position == 1
        } elseif ($report->verdict_time < 0 && !preg_match($dnfpattern, $result->time)) {
            // 2. Else If verdict_time < 0, Load Results before Position

            // Recurse from position to 1, break if position & time is unchanged
            $prevResults = Result::where('race_id', $report->race->id)
                                 ->where('position', '<', $result->position)
                                 ->orderBy('position', 'desc')
                                 ->get();

            // Check if position - 1's time > position's time, If yes -> position & time remains unchanged
            // If no -> Check if position - 1's time > update time, If yes -> update position-1's position
            for ($i = 0; $i < count($prevResults); $i++) {
                if (preg_match($dnfpattern, $prevResults[$i]->time)) {
                    break;
                } elseif (
                    $this->convertStandardtoMillis($prevResults[$i]->time) >
                    $this->convertStandardtoMillis($result->time)
                ) {
                    break;
                }

                if (
                    $this->convertStandardtoMillis($prevResults[$i]->time) >
                    $this->convertStandardtoMillis($result->time) + $report->verdict_time * 1000
                ) {
                    $updatedPos = $prevResults[$i]->position;

                    $prevResults[$i]->position += 1;
                    $prevResults[$i]->save();
                }
            }
        } elseif ($report->verdict_time > 0 && !preg_match($dnfpattern, $result->time)) {
            // 3. Else If verdict_time > 0, Load Results after Position

            // Recurse from position to Last Position, break if position & time is unchanged
            $nextResults = Result::where('race_id', $report->race->id)
                                 ->where('position', '>', $result->position)
                                 ->orderBy('position', 'asc')
                                 ->get();

            // Check if position + 1's time == +X Lap(s) OR DSQ OR DNF, break
            // If no -> Check if position + 1's time > update time, If yes -> update position+1's position
            for ($i = 0; $i < count($nextResults); $i++) {
                if (preg_match($dnfpattern, $nextResults[$i]->time)) {
                    break;
                } elseif (
                    $this->convertStandardtoMillis($nextResults[$i]->time) <
                    $this->convertStandardtoMillis($result->time)
                ) {
                    break;
                }

                if (
                    $this->convertStandardtoMillis($nextResults[$i]->time) <
                    $this->convertStandardtoMillis($result->time) + $report->verdict_time * 1000
                ) {
                    $updatedPos = $nextResults[$i]->position;

                    $nextResults[$i]->position -= 1;
                    $nextResults[$i]->save();
                } else {
                    break;
                }
            }
        }

        // Update position & time from index above
        // 1. If time == +X Lap(s) OR DSQ OR DNF, position & time remains unchanged
        if (!preg_match($dnfpattern, $result->time)) {
            $result->position = $updatedPos;
            $result->time = $this->convertMillisToStandard(
                $this->convertStandardtoMillis($result->time) +
                $report->verdict_time * 1000
            );
        }

        // Add verdict_pp to status
        $result->status = round($this->sgnp($result->status) * (abs($result->status) + $report->verdict_pp), 3);
        $result->save();

        // Reverse the change, because report is passed by reference.
        $report->verdict_time *= $mul;
        $report->verdict_pp *= $mul;

        return 0;
    }
}
