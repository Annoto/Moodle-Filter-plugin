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
 * HTML annoto text filter.
 *
 * @package    filter_annoto
 * @copyright  Annoto Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

class filter_annoto extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        global $CFG, $PAGE;

        // check if we run on course page
        if (!is_object($PAGE->cm)) {
            return $text;
        }

        // Get plugin global settings
        $settings = get_config('filter_annoto');
        
        // set id of the video frame where script should be attached
        $defaultplayerid = 'annoto_default_player_id';
        $playerid = $defaultplayerid;
        $playertype = '';
        $playerfound = false;
        $isglobalscope = filter_var($settings->scope, FILTER_VALIDATE_BOOLEAN);

        $textisempty = (!is_string($text) or empty($text));

        // If scope is not Global, check if url is in access list or if tag is present
        if(!$isglobalscope) {
            // ACL
            $acltext = ($settings->acl) ? $settings->acl : null ;
            $aclarr = preg_split("/\R/", $acltext);
            $iscourseinacl = false;
            $isurlinacl = false;

            if (is_object($PAGE->course)) {
                $iscourseinacl = in_array($PAGE->course->id, $aclarr);
            }
            if (!$iscourseinacl) {
                $pageurl = $PAGE->url->out();
                $isurlinacl = in_array($pageurl, $aclarr);
            }
            $isaclmatch = ($iscourseinacl || $isurlinacl);

            if (!$isaclmatch and ($textisempty or (stripos($text, '<annoto>') === false))) {
                return $text;
            }
        }
        
        // get login, logout urls
        $loginurl = $CFG->wwwroot . "/login/index.php";
        $logouturl = $CFG->wwwroot . "/login/logout.php?sesskey=" . sesskey();
        // get activity data for mediaDetails
        $cmtitle = $PAGE->cm->name;
        $cmintro = ($PAGE->activityrecord->intro) ? $PAGE->activityrecord->intro : '';
        // $currentgroupid = groups_get_activity_group($PAGE->cm);  // this function returns active group in current activity (most relevant option)
        // $currentgroupid = groups_get_activity_allowed_groups($PAGE->cm); // this function provides array of user's allowed groups in current course
        // $currentgroupname = groups_get_group_name($currentgroupid);

        // get course info
        if (is_object($PAGE->course)) {
            $courseId = $PAGE->course->id;
            $courseName = $PAGE->course->fullname;
            $courseSummary = $PAGE->course->summary;
        }

        // locale settings
        if ($settings->locale == "auto") {
            $lang = $this->get_lang();
        } else {
            $lang = $settings->locale;
        }
        $widgetposition = 'right';
        $widgetverticalalign = 'center';
        if (stripos($settings->widgetposition, 'left') !== false) {
            $widgetposition = 'left';
        }
        if (stripos($settings->widgetposition, 'top') !== false) {
            $widgetverticalalign = 'top';
        }
        if (stripos($settings->widgetposition, 'bottom') !== false) {
            $widgetverticalalign = 'bottom';
        }

        $jsparams = array(
            'bootstrapUrl' => $settings->scripturl,
            'clientId' => $settings->clientid,
            'userToken' => $this->get_user_token($settings),
            'position' => $widgetposition,
            'alignVertical' => $widgetverticalalign,
            'widgetOverlay' => $settings->widgetoverlay,
            'featureTab' => !empty($settings->tabs) ? filter_var($settings->tabs, FILTER_VALIDATE_BOOLEAN) : true,
            'featureCTA' => !empty($settings->cta) ? filter_var($settings->cta, FILTER_VALIDATE_BOOLEAN) : false,
            'loginUrl' => $loginurl,
            'logoutUrl' => $logouturl,
            'mediaTitle' => $cmtitle,
            'mediaDescription' => $cmintro,
            'mediaGroupId' => $courseId,
            'mediaGroupTitle' => $courseName,
            'mediaGroupDescription' => $courseSummary,
            'privateThread' => filter_var($settings->discussionscope, FILTER_VALIDATE_BOOLEAN),
            'locale' => $lang,
            'rtl' => filter_var((substr($lang, 0, 2) === "he"), FILTER_VALIDATE_BOOLEAN),
            'demoMode' => filter_var($settings->demomode, FILTER_VALIDATE_BOOLEAN),
            'defaultPlayerId' => $defaultplayerid,
            'zIndex' => !empty($settings->zindex) ? filter_var($settings->zindex, FILTER_VALIDATE_INT) : 100,
        );

        // Do a quick check using strpos to avoid unnecessary work
        if ($textisempty or ((stripos($text, '</video>') === false) and (stripos($text, '</iframe>') === false))) {
            // Give the front end script chance to find the player in cases when filter cannot
            $PAGE->requires->js_call_amd('filter_annoto/annoto-filter', 'init', array(false, $jsparams));
            return $text;
        }
        
        // get first player on the page
        if ($youtubepos = stripos($text, 'youtu')) {
            $pplayers['youtube'] = $youtubepos;
        }
        if ($vimeopos = stripos($text, 'vimeo')){
            $pplayers['vimeo'] = $vimeopos;
        }
        if ($videojspos = stripos($text, 'mediaplugin_videojs')) {
            $pplayers['videojs'] = $videojspos;
        }
        $firstplayerarr = array_keys($pplayers, min($pplayers));
        $firstplayer = $firstplayerarr[0];

        // attach annoto script to the first found player
        switch ($firstplayer) {
            case "youtube":
                $ytbidpattern = '%<iframe.*id=[\'"`]+([^\'"`]+)[\'"`].*<\/iframe>%i';
                preg_match($ytbidpattern, $text, $ytbidmatch);
                if (!empty($ytbidmatch)) {
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
                if (!empty($vmidmatch)) {
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
                // videojs media plugin always uses <video> html tag when embedding videos even if it's YouTube link
                // When the filter examins the page it sees the origin <video> videojs media plugin uses.
                // It is later replaced by <div> with same id (by videjs javascript) so we can use the id as playerId.
                $vjsidpattern = '%<video.*id=[\'"`]+([^\'"`]+)[\'"`].*<\/video>%i';
                preg_match($vjsidpattern, $text, $vjsidmatch);
                if (!empty($vjsidmatch)) {
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
        
        

        // Prepare data for including with filter
        $jsparams['playerType'] = $playertype;
        $jsparams['playerId'] = $playerid;

        $PAGE->requires->js_call_amd('filter_annoto/annoto-filter', 'init', array($playerfound, $jsparams));

        return $text;
    }

    private function get_user_token($settings) {
        global $USER, $PAGE;

        // is user logged in or is guest
        $userloggined = isloggedin();
        if (!$userloggined) {
            return '';
        }
        $guestuser = isguestuser();

        // Provide page and js with data
        // get user's avatar
        $userpicture = new user_picture($USER);
        $userpicture->size = 150;
        $userpictureurl = $userpicture->get_url($PAGE);

        // Create and encode JWT for Annoto script
        require_once('JWT.php');                    // Load JWT lib

        $issuedat = time();                       // Get current time
        $expire = $issuedat + 60 * 20;            // Adding 20 minutes

        // Check if user is a moderator
        $moderator = $this->is_moderator($settings);

        $payload = array(
            "jti" => $USER->id,                     // User's id in Moodle
            "name" => fullname($USER),              // User's fullname in Moodle
            "email" => $USER->email,                // User's email
            "photoUrl" => is_object($userpictureurl) ? $userpictureurl->out() : '',          // User's avatar in Moodle
            "iss" => $settings->clientid,           // clientID from global settings
            "exp" => $expire,                       // JWT token expiration time
            "scope" => ($moderator ? 'super-mod' : 'user'),
        );   
        
        return JWT::encode($payload, $settings->ssosecret);
    }

    private function get_lang() {
        global $PAGE, $SESSION, $COURSE, $USER;

        if (isset($COURSE->lang) and !empty($COURSE->lang)) {
            return $COURSE->lang;
        }
        if (isset($SESSION->lang) and !empty($SESSION->lang)) {
            return $SESSION->lang;
        }
        if (isset($USER->lang) and !empty($USER->lang)) {
            return $USER->lang;
         }
         return current_language();
    }

    private function is_moderator($settings) {
        global $COURSE, $USER;

        $reqcapabilities = array(
            'moodle/comment:delete',
            'moodle/question:add',
            'moodle/notes:manage',
            'moodle/course:manageactivities',
            'moodle/analytics:listinsights',);

        $coursecontext = context_course::instance($COURSE->id);

        // check the minimum required capabilities
        foreach($reqcapabilities as $cap) {
            if (!has_capability($cap, $coursecontext)) {
                return false;
            }
        }

        // Check if user is site admin. don't consider site admin as moderator..
        /* if (is_siteadmin($USER->id)) {
            return true;
        } */

        // Check if user has a role as defined in settings
        $userroles = get_user_roles($coursecontext, $USER->id, true);
        $allowedroles = explode(',', $settings->moderatorroles);

        foreach($userroles as $role) {
            if (in_array($role->roleid, $allowedroles)) {
                return true;
            }
        }

        return false;
    }
}