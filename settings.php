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
 * Settings.
 *
 * @package    filter_annoto
 * @copyright  Annoto Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Application Setup
    $settings->add(new admin_setting_heading('filter_annoto/setupheading', get_string('setupheading', 'filter_annoto'), ''));
    
    // API key / clientID
    $settings->add(new admin_setting_configtext('filter_annoto/clientid', get_string('clientid','filter_annoto'),
        get_string('clientiddesc', 'filter_annoto'), null));
    
    // SSO Secret
    $settings->add(new admin_setting_configtext('filter_annoto/ssosecret', get_string('ssosecret','filter_annoto'),
        get_string('ssosecretdesc', 'filter_annoto'), null));

    // Annoto scritp url
    $settings->add(new admin_setting_configtext('filter_annoto/scripturl', get_string('scripturl','filter_annoto'),
        get_string('scripturldesc', 'filter_annoto'), 'https://app.annoto.net/annoto-bootstrap.js'));

    // Demo checkbox
    $settings->add(new admin_setting_configcheckbox('filter_annoto/demomode', get_string('demomode','filter_annoto'),
        get_string('demomodedesc', 'filter_annoto'), 'true', 'true', 'false'));

// Application settings
    $settings->add(new admin_setting_heading('filter_annoto/appsetingsheading', get_string('appsetingsheading', 'filter_annoto'), ''));

    // Call To Action
    /* $settings->add(new admin_setting_configcheckbox('filter_annoto/cta', get_string('cta','filter_annoto'),
        get_string('ctadesc', 'filter_annoto'), 'false', 'true', 'false')); */

    // Locale
    $settings->add(new admin_setting_configselect('filter_annoto/locale', get_string('locale','filter_annoto'),
        get_string('localedesc', 'filter_annoto'), 'auto', array(  'auto' => get_string('localeauto','filter_annoto'),
                                                                'en' => get_string('localeen','filter_annoto'),
                                                                'he' => get_string('localehe','filter_annoto'))));

    // Discussions Scope 
    $settings->add(new admin_setting_configselect('filter_annoto/discussionscope', get_string('discussionscope','filter_annoto'),
        get_string('discussionscopedesc', 'filter_annoto'), 'true', array(  'false' => get_string('discussionscopesitewide','filter_annoto'),
                                                                    'true' => get_string('discussionscopeprivate','filter_annoto'))));

    // Moderators Roles
    $settings->add(new admin_setting_pickroles('filter_annoto/moderatorroles', get_string('moderatorroles','filter_annoto'),
        get_string('moderatorrolesdesc','filter_annoto'),
        array(
            'manager',
            'coursecreator',
            'editingteacher',
        )));
    
    
// UX preferences
    $settings->add(new admin_setting_heading('filter_annoto/appuxheading', get_string('appuxheading', 'filter_annoto'), ''));

    // Widget position
    $settings->add(new admin_setting_configselect('filter_annoto/widgetposition', get_string('widgetposition','filter_annoto'),
        get_string('widgetpositiondesc', 'filter_annoto'), 'topright', array(   'right' => get_string('positionright','filter_annoto'),
                                                                                'left' => get_string('positionleft','filter_annoto'),
                                                                                'topright' => get_string('positiontopright','filter_annoto'),
                                                                                'topleft' => get_string('positiontopleft','filter_annoto'),
                                                                                'bottomright' => get_string('positionbottomright','filter_annoto'),
                                                                                'bottomleft' => get_string('positionbottomleft','filter_annoto'))));
    // Widget overlay mode
    $settings->add(new admin_setting_configselect('filter_annoto/widgetoverlay', get_string('widgetoverlay','filter_annoto'),
        get_string('widgetoverlaydesc', 'filter_annoto'), 'auto', array(    'auto' => get_string('overlayauto','filter_annoto'),
                                                                            'inner' => get_string('overlayinner','filter_annoto'),
                                                                            'outer' => get_string('overlayouter','filter_annoto'))));
    // Tabs
    $settings->add(new admin_setting_configcheckbox('filter_annoto/tabs', get_string('tabs','filter_annoto'),
        get_string('tabsdesc', 'filter_annoto'), 'true', 'true', 'false'));

    // Annoto zindex
    $settings->add(new admin_setting_configtext('filter_annoto/zindex', get_string('zindex','filter_annoto'),
        get_string('zindexdesc', 'filter_annoto'), 100, PARAM_INT));

// ACL and scope
    $settings->add(new admin_setting_heading('filter_annoto/aclheading', get_string('aclheading', 'filter_annoto'), ''));

    // Global Scope
    $settings->add(new admin_setting_configcheckbox('filter_annoto/scope', get_string('scope','filter_annoto'),
        get_string('scopedesc', 'filter_annoto'), 'false', 'true', 'false'));

    // URL ACL
    $settings->add(new admin_setting_configtextarea('filter_annoto/acl', get_string('acl','filter_annoto'),
        get_string('acldesc', 'filter_annoto'), null));
