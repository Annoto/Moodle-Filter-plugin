<?php

// This file is part of Annoto/moodle-filter-plugin - http://annoto.net/
// https://github.com/Annoto/moodle-filter-plugin
// Annoto/moodle-filter-plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Annoto/moodle-filter-plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Annoto/moodle-filter-plugin.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings.
 *
 * @package    filter_annoto
 * @copyright  Annoto Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

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

    // Widget position
    $settings->add(new admin_setting_configselect('filter_annoto/widgetposition', get_string('widgetposition','filter_annoto'),
    get_string('widgetpositiondesc', 'filter_annoto'), 'right', array(  'right' => get_string('widgetpositionright','filter_annoto'),
                                                                        'left' => get_string('widgetpositionleft','filter_annoto'))));

    // Tabs
    $settings->add(new admin_setting_configcheckbox('filter_annoto/tabs', get_string('tabs','filter_annoto'),
    get_string('tabsdesc', 'filter_annoto'), 'false', 'true', 'false'));

    // Call To Action
    $settings->add(new admin_setting_configcheckbox('filter_annoto/cta', get_string('cta','filter_annoto'),
    get_string('ctadesc', 'filter_annoto'), 'false', 'true', 'false'));

    // Locale
    $settings->add(new admin_setting_configselect('filter_annoto/locale', get_string('locale','filter_annoto'),
    get_string('localedesc', 'filter_annoto'), 'auto', array(  'auto' => get_string('localeauto','filter_annoto'),
                                                                'en' => get_string('localeen','filter_annoto'),
                                                                'he' => get_string('localehe','filter_annoto'))));
    
    // Global Scope
    $settings->add(new admin_setting_configcheckbox('filter_annoto/scope', get_string('scope','filter_annoto'),
    get_string('scopedesc', 'filter_annoto'), 'true', 'true', 'false'));
    
    // Discussions Scope 
    $settings->add(new admin_setting_configselect('filter_annoto/discussionscope', get_string('discussionscope','filter_annoto'),
    get_string('discussionscopedesc', 'filter_annoto'), 'false', array(  'false' => get_string('discussionscopesitewide','filter_annoto'),
                                                                   'true' => get_string('discussionscopeprivate','filter_annoto'))));

    // URL ACL
    $settings->add(new admin_setting_configtextarea('filter_annoto/urlacl', get_string('urlacl','filter_annoto'),
    get_string('urlacldesc', 'filter_annoto'), null));
