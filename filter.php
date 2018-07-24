<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * HTML  tidy text filter.
 *
 * @package    filter_annoto
 * @copyright  Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_annoto extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        global $CFG, $USER, $PAGE, $cm, $page;


        // Get plugin global settings
        $settings = get_config('filter_annoto');

        // set id of the video frame where script should be attached
        $playerid = "annotoscript";
        $playertype = "undefined";
        $playerfound = false;

        // Annoto's scritp url
        $scripturl = $settings->scripturl;

        // is user logged in or is guest
        $userloggined = isloggedin();
        $guestuser = isguestuser();
        $loginurl = $CFG->wwwroot . "/login/index.php";
        $logouturl = $CFG->wwwroot . "/login/logout.php?sesskey=" . sesskey();

        // Do a quick check using strpos to avoid unnecessary work
        if (!is_string($text) or empty($text)) {
            return $text;
        }
        // Do check if iframe or video and annoto tags are present
        if (!(stripos($text, '</video>') or stripos($text, '</iframe>'))) {
            return $text;
        }

        // check if Scope is for all site (false) or for pages with tag only (true) -- booleans here are strings for js compatibility
        if ($settings->scope === 'true') {       
            // check if annoto is turned on
            if (!stripos($text, '<annoto>')) {
                return $text;
            }
        }
        
        // get first player on the page
        if ($youtubepos = stripos($text, 'youtu')) {
            $pplayers['youtube'] = $youtubepos;
        }
        if ($vimeopos = stripos($text, 'vimeo')){
            $pplayers['vimeo'] = $vimeopos;
        }
        if ($videojspos = stripos($text, 'mediaplugin')) {
            $pplayers['videojs'] = $videojspos;
        }
        $firstplayerarr = array_keys($pplayers, min($pplayers));
        $firstplayer = $firstplayerarr[0];

        // attach annoto script to the first found player
        switch ($firstplayer) {
            case "youtube":
                $ytbidpattern = '%<iframe.*id=[\'"`]+([^\'"`]+)[\'"`].*<\/iframe>%i';
                preg_match($ytbidpattern, $text, $ytbidmatch);
                if (!empty($ytbidmatch) && !$playerfound) {
                    $playerid = $ytbidmatch[1];
                }

                $youtubepattern = "%(<iframe)(.*youtube)%i";
                preg_match($youtubepattern, $text, $ytbpmatch);
                if (!empty($ytbpmatch)) {
                    $text = preg_replace($youtubepattern, "<iframe id='".$playerid."'$2", $text, 1);
                    $playertype = "youtube";
                    $playerfound = true;
                }
                break;

            case "vimeo":
                $vmidpattern = '%<iframe.*id=[\'"`]+([^\'"`]+)[\'"`].*<\/iframe>%i';
                preg_match($vmidpattern, $text, $vmidmatch);
                if (!empty($vmidmatch) && !$playerfound) {
                    $playerid = $vmidmatch[1];
                }

                $vimeopattern = "%(<iframe)(.*vimeo)%i";
                preg_match($vimeopattern, $text, $vmpmatch);
                if (!empty($vmpmatch)) {
                    $text = preg_replace($vimeopattern, "<iframe id='".$playerid."'$2", $text, 1);
                    $playertype = "vimeo";
                    $playerfound = true;
                }
                break;

            case "videojs":
                $vjsidpattern = '%<video.*id=[\'"`]+([^\'"`]+)[\'"`].*<\/video>%i';
                preg_match($vjsidpattern, $text, $vjsidmatch);
                if (!empty($vjsidmatch) && !$playerfound) {
                    $playerid = $vjsidmatch[1];
                }
                
                $vjspattern = "%(<video)%i";
                preg_match($vjspattern, $text, $vjspmatch);
                if (!empty($vjspmatch)) {
                    $text = preg_replace($vjspattern, "<video id='".$playerid."'", $text, 1);
                    $playertype = "videojs";
                    $playerfound = true;
                }
                break;
        }
        
        // Provide page and js with data
        // get user's avatar
        $userpicture = new user_picture($USER);
        $userpicture->size = 150;
        $userpictureurl = $userpicture->get_url($PAGE);

        // get activity data for mediaDetails
        $cmtitle = $PAGE->cm->name;
        $cmintro = ($page->intro) ? $page->intro : '';
        $currentgroupid = '';
        $currentgroupid = groups_get_activity_group($cm);  // this function returns active group in current activity (moste relevant option)
        // $currentgroupid = groups_get_activity_allowed_groups($cm); // this function provides array of user's allowed groups in current course
        $currentgroupname = '';
        $currentgroupname = groups_get_group_name($currentgroupid);

        // locale settings
        if ($settings->locale == "auto") {
            $lang = current_language();
        } else {
            $lang = $settings->locale;
        }
        // define rtl property according to locale setting
        $rtl = ($lang == "he") ? "true" : "false";

        // Create and encode JWT for Annoto script
        require_once('JWT.php');                    // Load JWT lib

        $issuedAt   = time();                       // Get current time
        $expire     = $issuedAt + 60*20;            // Adding 20 minutes

        $payload= array(
            "jti" => $USER->id,                     // User's id in Moodle
            "name" => fullname($USER),              // User's fullname in Moodle
            "email" => $USER->email,                // User's email
            "photoUrl" => $userpictureurl,          // User's avatar in Moodle
            "iss" => $settings->clientid,           // clientID from global settings
            "exp" => $expire                        // JWT token expiration time
        );
        
        $key = $settings->ssosecret;                // SSO secret from global settings        
        $jwt = JWT::encode($payload, $key);         // Create and encode JWT for Annoto script

        // empty jwt for not loggined users and guests
        if (!$userloggined || $guestuser) {
            $jwt = '';
        }

        // Prepare data for including with filter
        $annoto = <<<EOT
        <script src="{$scripturl}"></script>
        <script>
            var config = {
                clientId: '{$jwt}',
                position: '{$settings->widgetposition}',
                features: {
                    tabs: $settings->tabs,
                    cta:  $settings->cta,
                },
                ux :{
                    ssoAuthRequestHandle: function() {
                        window.location.replace('{$loginurl}')
                    },
                    logoutRequestHandle: function() {
                        window.location.replace('{$logouturl}')
                    }
                },
                widgets: [
                    {
                        player: {
                            type: '{$playertype}',
                            element: '{$playerid}',                  /* DOM element id of the player demo-yt-player */
                            MediaDetails :  function() {
                              return {
                                title : '$cmtitle',
                                description: '$cmintro',                // (Optional) Media description
                                thumbnails: '',
                                authorName: '',
                                group: {                             // (Optional) Course/group
                                    id: '$currentgroupid',            // Unique group identifier
                                    type: 'playlist',               // playlist is the default playlist | users
                                    title: '$currentgroupname',       // Group title
                                    privateThread: $settings->discussionscope,  // false by default. If set to true the The discussion will be private to the group.
                                    description: '$currentgroupname',  // (Optional) Group description
                                    thumbnails: '',
                                },
                            }},
                        },
                        timeline: {
                            embedded: false,
                            overlayVideo: false
                        },
                        demoDiscussion: 'portals-showcase',
                        openOnLoad: true,
                    }
                ],
                thread: {
                    showReplies: true
                },
                demoMode: {$settings->demomode},
                rtl: {$rtl},
                locale: '{$lang}'
            };

        </script>

EOT;

        $text .= $annoto;

        
        return $text;
    }
}