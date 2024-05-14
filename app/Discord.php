<?php

// Check if user is in IRC or not
namespace App;

use Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;

class Discord
{
    protected $irc_guild;
    protected $applicantRole;
    protected $profilesChannel;
    protected $notificationChannel;

    public function __construct()
    {
        $this->irc_guild = (int)config('services.discord.irc_guild');
        $this->applicantRole = (int)config('services.discord.applicant_role');
        $this->profilesChannel = (int)config('services.discord.profiles_channel');
        $this->notificationChannel = (int)config('services.discord.notification_channel');
    }

    public function check($userr)
    {
        $params = (['access_token' => $userr->accessTokenResponseBody['access_token'],]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://discord.com/api/users/@me/guilds",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer " . $params['access_token']
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            $irc = $this->irc_guild;
            $check = 'False';
            for ($i = 0; $i < count($final); $i++) {
                if ($final[$i]['id'] == $irc) {
                    $check = 'True';
                }
            }
            return $check;
        }
    }

    public function getroles($id)
    {
        $params = (['token' => config('services.discord.bot')]);

        $curl = curl_init();
        $server = $this->irc_guild;

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://discord.com/api/guilds/" . $server . "/roles",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bot " . $params['token']
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            return $this->checkRoles($final, $id);
        }
    }

    public function checkRoles($roles, $id)
    {
        $data = $this->getMemberRoles($id);
        if ($data != "Invalid") {
            $arr = array();

            $crole = 0;
            for ($i = 0; $i < count($roles); $i++) {
                for ($j = 0; $j < count($data); $j++) {
                    if ($roles[$i]['id'] == $data[$j]) {
                        array_push($arr, ['name' => $roles[$i]['name'],
                                          'color' => dechex($roles[$i]['color'])]);
                        $crole++;
                    }
                }
            }
            return $arr;
        } else {
            return "Error Fetching Roles";
        }
    }

    public function getMemberRoles($id)
    {
        $userdata = $id;
        $params = (['token' => config('services.discord.bot')]);

        $curl = curl_init();
        $server = $this->irc_guild;
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://discord.com/api/guilds/" . $server . "/members/" . $userdata,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bot " . $params['token']
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $final['roles'];
            }
        }
    }

    public function removeApplicantRole($id, $applicantrole)
    {
        $params = (['token' => config('services.discord.bot')]);

        $curl = curl_init();
        $server = $this->irc_guild;
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://discord.com/api/guilds/" . $server . "/members/" . $id . "/roles/" . $applicantrole,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bot " . $params['token']
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $response;
            }
        }
    }

    public function addroles($roles, $id, $data)
    {
        $params = (['token' => config('services.discord.bot')]);
        if (in_array($this->applicantRole, $data)) {
            $var =  $this->removeApplicantRole($id, $this->applicantRole);
            if ($var == "Invalid") {
                return "Error Removing applicant role";
            }
        }

        if (!empty($roles)) {
            foreach ($roles as $value) {
                sleep(1);
                $curl = curl_init();
                $server = $this->irc_guild;
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://discord.com/api/guilds/" . $server . "/members/" . $id . "/roles/" . $value,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: 0',
                "Authorization: Bot " . $params['token']
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
            }

            if ($err) {
                return $err;
            } else {
                $final = json_decode($response, true);
                return $response;

                if (isset($final['message'])) {
                    return "Invalid";
                } else {
                    return $final;
                }
            }
        }
    }

    public function sendMemberProfile($message)
    {
        $adata = array("content" => $message, "tts" => false);
        $postdata = json_encode($adata);

        $discord_id = Auth::user()->discord_id;
        $params = (['token' => config('services.discord.bot')]);
        $curl = curl_init();

        $profileschannel = $this->profilesChannel;

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://discord.com/api/channels/" . $profileschannel . "/messages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postdata,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bot " . $params['token']
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $response;
            }
        }
    }

    public function updatedetails()
    {
        $users = User::select('discord_id', 'id')->get();
        foreach ($users as $user) {
            sleep(2);
            $params = (['token' => config('services.discord.bot')]);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://discord.com/api/users/ " . $user->discord_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bot " . $params['token']
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return $err;
            } else {
                  $final = json_decode($response, true);
                  $this->savedetails($final, $user->id);
            }
        }
        return "Done";
    }

    public function savedetails($final, $userid)
    {
        if (isset($final['username']) && $final['avatar'] == null) {
            $avatar = "https://cdn.discordapp.com/embed/avatars/3.png";
            DB::table('users')
              ->where('id', $userid)
              ->update([
                  'name' => $final['username'],
                  'avatar' => $avatar,
                  'discord_discrim' => $final['discriminator']
            ]);
        }

        if (isset($final['message'])) {
            $avatar = "https://cdn.discordapp.com/embed/avatars/3.png";
            DB::table('users')
            ->where('id', $userid)
            ->update([
                'avatar' => $avatar,
            ]);
        }

        if (isset($final['avatar']) && $final['avatar'] != null) {
            $avatar = "https://cdn.discordapp.com/avatars/" . $final['id'] . "/" . $final['avatar'] . ".jpg";
            DB::table('users')
              ->where('id', $userid)
              ->update([
                  'name' => $final['username'],
                  'avatar' => $avatar,
                  'discord_discrim' => $final['discriminator']
            ]);
        }
    }

    public function notifysignup($season)
    {
        $seasonname = Season::where('id', $season)->select('game', 'name')->get()->toArray();

        $discordid = Auth::user()->discord_id;
        $sname = $seasonname[0]['name'];
        $gname = $seasonname[0]['game'];
        $message = "<@$discordid> has signed up for **$sname** !";
        $adata = array("content" => $message, "tts" => false);
        $postdata = json_encode($adata);

        $params = (['token' => config('services.discord.bot')]);
        $curl = curl_init();

        $esports = $this->notificationChannel;

        curl_setopt_array($curl, array(
           CURLOPT_URL => "https://discord.com/api/channels/" . $esports . "/messages",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "POST",
           CURLOPT_POSTFIELDS => $postdata,
           CURLOPT_HTTPHEADER => array(
               'Content-Type: application/json',
               "Authorization: Bot " . $params['token']
           ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $response;
            }
        }
    }

    public static function publishMessage($message, $channel)
    {
        if ($channel == null) {
            return 0;
        }
        $adata = array("content" => $message, "tts" => false);
        $postdata = json_encode($adata);
        $params = (['token' => config('services.discord.bot')]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
           CURLOPT_URL => "https://discord.com/api/channels/" . $channel . "/messages",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "POST",
           CURLOPT_POSTFIELDS => $postdata,
           CURLOPT_HTTPHEADER => array(
               'Content-Type: application/json',
               "Authorization: Bot " . $params['token']
           ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $final['id'];
            }
        }
    }


    public static function editMessage($message, $channel, $msgid)
    {
        if ($channel == null) {
            return 0;
        }
        $adata = array("content" => $message, "tts" => false);
        $postdata = json_encode($adata);
        $params = (['token' => config('services.discord.bot')]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
           CURLOPT_URL => "https://discord.com/api/channels/" . $channel . "/messages/" . $msgid,
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "PATCH",
           CURLOPT_POSTFIELDS => $postdata,
           CURLOPT_HTTPHEADER => array(
               'Content-Type: application/json',
               "Authorization: Bot " . $params['token']
           ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $final;
            }
        }
    }

    public static function deleteMessage($channel, $msgid)
    {
        if ($channel == null) {
            return 0;
        }
        $params = (['token' => config('services.discord.bot')]);
        $curl = curl_init();

        curl_setopt_array($curl, array(
           CURLOPT_URL => "https://discord.com/api/channels/" . $channel . "/messages/" . $msgid,
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "DELETE",
           CURLOPT_HTTPHEADER => array(
               'Content-Type: application/json',
               "Authorization: Bot " . $params['token']
           ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                // Sucess returns a empty 204 response put whatever you want to return here!
                return "Done!";
            }
        }
    }

    public function getServerDetails()
    {
        $server = $this->irc_guild;
        $params = (['token' => config('services.discord.bot')]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://discord.com/api/guilds/" . $server . "?with_counts=true",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bot " . $params['token']
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $final = json_decode($response, true);
            if (isset($final['message'])) {
                return "Invalid";
            } else {
                return $final;
            }
        }
    }

    public function getMembersByRoles($roles)
    {
        $serverDetails = $this->getServerDetails();
        $serverRoles = $serverDetails['roles'];
        $limit = ceil($serverDetails['approximate_member_count'] / 1000);
        $limit = intval($limit);
        $users = array();
        $after = 0;
        $params = (['token' => config('services.discord.bot')]);
        $server = $this->irc_guild;
        for ($i = 0; $i < $limit; $i++) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://discord.com/api/guilds/" . $server . "/members?limit=1000&after=" . $after,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    "Authorization: Bot " . $params['token']
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return $err;
            } else {
                $final = json_decode($response, true);
                if (isset($final['message'])) {
                    return "Invalid";
                } else {
                    array_push($users, $final);
                    $lastElement = end($final);
                    $after = $lastElement['user']['id'];
                }
            }
        }
        $users = array_merge(...$users);

        // Fetch color of all the roles we are about to display
        for ($i = 0; $i < count($roles); $i++) {
            $roles[$i]["role_color"] = "";
            $roles[$i]["icon"] = null;
            for ($j = 0; $j < count($serverRoles); $j++) {
                if ($roles[$i]['role_id'] == $serverRoles[$j]['id']) {
                    $roles[$i]["role_color"] = str_pad(dechex($serverRoles[$j]['color']), 6, "0", STR_PAD_LEFT);
                    break;
                }
            }
        }

        for ($i = 0; $i < count($roles); $i++) {
            $roles[$i]["users"] = array();
            for ($j = 0; $j < count($users); $j++) {
                if (in_array($roles[$i]['role_id'], $users[$j]['roles'])) {
                    $pushThis = array(
                        "name" => $users[$j]['user']['username'],
                        "avatar" => "https://cdn.discordapp.com/avatars/" . $users[$j]['user']['id'] . "/" . $users[$j]['user']['avatar']
                    );
                    array_push($roles[$i]["users"], $pushThis);
                }
            }
        }

        return $roles;
    }
}
