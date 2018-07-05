<?php

defined('MOODLE_INTERNAL') || die;

    // API key / clientID
    $settings->add(new admin_setting_configtext('filter_annoto/clientid', get_string('clientid','filter_annoto'),
                   get_string('clientiddesc', 'filter_annoto'), null));
    
    //SSO Secret
    $settings->add(new admin_setting_configtext('filter_annoto/ssosecret', get_string('ssosecret','filter_annoto'),
    get_string('ssosecretdesc', 'filter_annoto'), null));

    // Demo checkbox
    $settings->add(new admin_setting_configcheckbox('filter_annoto/demomode', get_string('demomode','filter_annoto'),
    get_string('demomodedesc', 'filter_annoto'), 'true', 'true', 'false'));

    //Widget position
    $settings->add(new admin_setting_configselect('filter_annoto/widgetposition', get_string('widgetposition','filter_annoto'),
    get_string('widgetpositiondesc', 'filter_annoto'), 'right', array(  'right' => get_string('widgetpositionright','filter_annoto'),
                                                                        'left' => get_string('widgetpositionleft','filter_annoto'))));

    // Tabs
    $settings->add(new admin_setting_configcheckbox('filter_annoto/tabs', get_string('tabs','filter_annoto'),
    get_string('tabsdesc', 'filter_annoto'), 'true', 'true', 'false'));

    // Call To Action
    $settings->add(new admin_setting_configcheckbox('filter_annoto/cta', get_string('cta','filter_annoto'),
    get_string('ctadesc', 'filter_annoto'), 'true', 'true', 'false'));

    //Locale
    $settings->add(new admin_setting_configselect('filter_annoto/locale', get_string('locale','filter_annoto'),
    get_string('localedesc', 'filter_annoto'), 'auto', array(  'auto' => get_string('localeauto','filter_annoto'),
                                                                'en' => get_string('localeen','filter_annoto'),
                                                                'he' => get_string('localehe','filter_annoto'))));
    
    //Discussions Scope
    $settings->add(new admin_setting_configcheckbox('filter_annoto/scope', get_string('scope','filter_annoto'),
    get_string('scopedesc', 'filter_annoto'), 'false', 'true', 'false'));
    //$settings->add(new admin_setting_configselect('filter_annoto/scope', get_string('scope','filter_annoto'),
    //get_string('scopedesc', 'filter_annoto'), 'sitewide', array(  'sitewide' => get_string('scopesitewide','filter_annoto'),
       //                                                             'private' => get_string('scopeprivate','filter_annoto'))));