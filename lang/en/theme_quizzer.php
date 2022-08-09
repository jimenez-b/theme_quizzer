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
 
// A description shown in the admin theme selector.                                                                                 
$string['choosereadme'] = 'Theme quizzer is a child theme of Boost.';
// The name of our plugin.
$string['pluginname'] = 'Quizzer';
// We need to include a lang string for each block region.
$string['region-side-pre'] = 'Right';

// The name of the second tab in the theme settings.                                                                                
$string['advancedsettings'] = 'Advanced settings';                                                                                  
// The brand colour setting.                                                                                                        
$string['brandcolor'] = 'Brand colour';                                                                                             
// The brand colour setting description.                                                                                            
$string['brandcolor_desc'] = 'The accent colour.';     
// Name of the settings pages.                                                                                                      
$string['configtitle'] = 'Quizzer settings';                                                                                          
// Name of the first settings tab.                                                                                                  
$string['generalsettings'] = 'General settings';                                                                                                   
// Preset files setting.                                                                                                            
$string['presetfiles'] = 'Additional theme preset files';                                                                           
// Preset files help text.                                                                                                          
$string['presetfiles_desc'] = 'Preset files can be used to dramatically alter the appearance of the theme. See <a href=https://docs.moodle.org/dev/Boost_Presets>Boost presets</a> for information on creating and sharing your own preset files, and see the <a href=http://moodle.net/boost>Presets repository</a> for presets that others have shared.';
// Preset setting.                                                                                                                  
$string['preset'] = 'Theme preset';                                                                                                 
// Preset help text.                                                                                                                
$string['preset_desc'] = 'Pick a preset to broadly change the look of the theme.';                                                  
// Raw SCSS setting.                                                                                                                
$string['rawscss'] = 'Raw SCSS';                                                                                                    
// Raw SCSS setting help text.                                                                                                      
$string['rawscss_desc'] = 'Use this field to provide SCSS or CSS code which will be injected at the end of the style sheet.';       
// Raw initial SCSS setting.                                                                                                        
$string['rawscsspre'] = 'Raw initial SCSS';                                                                                         
// Raw initial SCSS setting help text.                                                                                              
$string['rawscsspre_desc'] = 'In this field you can provide initialising SCSS code, it will be injected before everything else. Most of the time you will use this setting to define variables.';

//Last Saved string - to be shown in the attempt page
$string['lastautosave'] = "Last saved: ";

//Footer strings
$string['footercopyright'] = "© Concordia University";
$string['footerfaq']       = "Visit concordia.ca/cole for Frequently Asked Questions about the COLE exam site as well as tutorial videos covering its many features";
$string['footersupport']   = "Having technical difficulties during an exam? Call <strong>1-888-202-8615</strong> for support.";

$string['unsureattempt'] = 'Unsure';

$string['flagged'] = 'Flagged';
$string['notflagged'] = 'Not flagged';
//$string['clickflag'] = 'Flag question';
$string['clicktoflag'] = 'Flag this question for future reference';
//$string['clickunflag'] = 'Remove flag';

//navigation buttons specific strings
$string['navigatepreviousshort'] = 'Prev.';
$string['navigatenextshort'] = 'Next';

//Summary strings
//Table strings
$string['questionno'] = 'Q No.';
$string['questionsatt'] = 'Questions attempted:';
//Filter
$string['sortby'] = 'Filter by';

//color boxes
$string['question_key'] = 'Key';
$string['answered'] = 'Attempted';
$string['correct'] = 'Correct';
$string['unsure'] = 'Unsure of attempt';
$string['unsureshort'] = 'Unsure';
$string['unanswered'] = 'Not attempted';
$string['invalidanswer'] = 'Incomplete';
$string['incorrect'] = 'Incorrect';
$string['filterall'] = 'All';
$string['partiallycorrect'] = 'Partially correct';
$string['gradingrequired'] = 'Requires grading';
$string['notanswered'] = 'Not answered';
$string['invalidanswerhybrid'] = 'Incomplete/Pending upload';
$string['invalidsummaryhybrid'] = 'Pending upload';
$string['invalidsummaryhybridbutton'] = 'Incomplete/ To upload';

//landing quiz
$string['testattempts'] = 'Attempts allowed';

//review page 
$string['modaltext'] = 'Your exam has been submitted successfully for marking. Please wait for a few seconds, and you will be returned to the exam cover page.';
$string['modaltextextra'] = 'We are having difficulty closing your exam, please notify an invigilator.';

//qr upload page 
$string['qruploadmodaltext'] = 'This section of the exam has finished. In a few seconds you will be redirected to the exam start page where you will scan the QR code and upload your files.';