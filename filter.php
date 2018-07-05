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

        // Get plugin global settings
        $settings = get_config('filter_annoto');

        // set id of the video frame where script should be attached
        $playerid = "annotoscript";
        $playertype = "undefined";

        // Do a quick check using strpos to avoid unnecessary work
        if (!is_string($text) or empty($text)) {
            return $text;
        }

        // Do check if iframe or video and annoto tags are present
        if (!(stripos($text, '</video>') or stripos($text, '</iframe>'))) {
            return $text;
        }

        // check if annoto is turned on
        if (!stripos($text, '<annoto>')) {
            return $text;
        }

        // youtube ifarme detector
        $youtubepattern = "%(<iframe)(.*youtube)%i";
        preg_match($youtubepattern, $text, $match);
        if (!empty($match)) {
            $text = preg_replace($youtubepattern, "<iframe id='".$playerid."'$2", $text);
            $playertype = "youtube";
        }

        // vimeo ifarme detector
        $vimeopattern = "%(<iframe)(.*vimeo)%i";
        preg_match($vimeopattern, $text, $match);
        if (!empty($match)) {
            $text = preg_replace($vimeopattern, "<iframe id='".$playerid."'$2", $text);
            $playertype = "vimeo";
        }

        // videojs detector
        $vjspattern = "%(<video)%i";
        preg_match($vjspattern, $text, $match);
        if (!empty($match)) {
            $text = preg_replace($vjspattern, "<video id='".$playerid."'", $text);
            $playertype = "videojs";
        }

        // Provide page and js with data
        global $USER, $PAGE;

        // get user's avatar
        $userpicture = new user_picture($USER);
        $userpicture->size = 150;
        $userpictureurl = $userpicture->get_url($PAGE);

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

        // Prepare data for including with filter
        $annoto = <<<EOT
        <script src="https://app.annoto.net/annoto-bootstrap.js"></script>
        <script>
            var config = {
                clientId: '{$jwt}',
                position: '{$settings->widgetposition}',
                features: {
                    tabs: $settings->tabs,
                    cta:  $settings->cta,
                },
                widgets: [
                    {
                        player: {
                            type: '{$playertype}',
                            element: '{$playerid}'  /* DOM element id of the player demo-yt-player */
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

            window.onload = function () {
                if (window.Annoto) {
                    console && console.info('Annoto: Boot');
                    window.Annoto.on('ready', function (api) {
                        if (api) {
                            console && console.info('Annoto: got api! %O', api);
                        }
                    });
                    window.Annoto.boot(config);
                } else {
                    console && console.error('Annoto: not loaded');
                }
            }
        </script>

EOT;

        $text .= $annoto;

        
        return $text;
    }
}