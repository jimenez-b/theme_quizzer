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

// This line protects the file from being accessed by a URL directly.                                                               
defined('MOODLE_INTERNAL') || die();                                                                                                
 
// This is used for performance, we don't need to know about these settings on every page in Moodle, only when                      
// we are looking at the admin settings pages.                                                                                      
if ($ADMIN->fulltree) {                                                                                                             
 
    // Boost provides a nice setting page which splits settings onto separate tabs. We want to use it here.                         
    $settings = new theme_boost_admin_settingspage_tabs('themesettingquizzer', get_string('configtitle', 'theme_quizzer'));             
 
    // Each page is a tab - the first is the "General" tab.                                                                         
    $page = new admin_settingpage('theme_quizzer_general', get_string('generalsettings', 'theme_quizzer'));                             
 
    // Replicate the preset setting from boost.                                                                                     
    $name = 'theme_quizzer/preset';                                                                                                   
    $title = get_string('preset', 'theme_quizzer');                                                                                   
    $description = get_string('preset_desc', 'theme_quizzer');                                                                        
    $default = 'default.scss';                                                                                                      
 
    // We list files in our own file area to add to the drop down. We will provide our own function to                              
    // load all the presets from the correct paths.                                                                                 
    $context = context_system::instance();                                                                                          
    $fs = get_file_storage();                                                                                                       
    $files = $fs->get_area_files($context->id, 'theme_quizzer', 'preset', 0, 'itemid, filepath, filename', false);                    
 
    $choices = [];                                                                                                                  
    foreach ($files as $file) {                                                                                                     
        $choices[$file->get_filename()] = $file->get_filename();                                                                    
    }                                                                                                                               
    // These are the built in presets from Boost                                                       
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';
    $choices['cole.scss'] = 'cole.scss';
    
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);                                     
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Preset files setting.                                                                                                        
    $name = 'theme_quizzer/presetfiles';                                                                                              
    $title = get_string('presetfiles','theme_quizzer');                                                                               
    $description = get_string('presetfiles_desc', 'theme_quizzer');                                                                   
 
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,                                         
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));                                                               
    $page->add($setting);     
 
    // Variable $brand-color.                                                                                                       
    // We use an empty default value because the default colour should come from the preset.                                        
    $name = 'theme_quizzer/brandcolor';                                                                                               
    $title = get_string('brandcolor', 'theme_quizzer');                                                                               
    $description = get_string('brandcolor_desc', 'theme_quizzer');                                                                    
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');                                               
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Must add the page after definiting all the settings!                                                                         
    $settings->add($page);                                                                                                          
 
    // Advanced settings.                                                                                                           
    $page = new admin_settingpage('theme_quizzer_advanced', get_string('advancedsettings', 'theme_quizzer'));                           
 
    // Raw SCSS to include before the content.                                                                                      
    $setting = new admin_setting_configtextarea('theme_quizzer/scsspre',                                                              
        get_string('rawscsspre', 'theme_quizzer'), get_string('rawscsspre_desc', 'theme_quizzer'), '', PARAM_RAW);                      
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    // Raw SCSS to include after the content.                                                                                       
    $setting = new admin_setting_configtextarea('theme_quizzer/scss', get_string('rawscss', 'theme_quizzer'),                           
        get_string('rawscss_desc', 'theme_quizzer'), '', PARAM_RAW);                                                                  
    $setting->set_updatedcallback('theme_reset_all_caches');                                                                        
    $page->add($setting);                                                                                                           
 
    $settings->add($page);                                                                                                          
}