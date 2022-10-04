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
$string['footerfaq']       = "Visit <strong><a href='https://concordia.ca/cole' target='_blank'>concordia.ca/cole</a></strong> for Frequently Asked Questions about the COLE exam site as well as tutorial videos covering its many features";
$string['footersupport']   = "Having technical difficulties during an exam?";

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

/**
 * strings copied from conquizzer plugin to keep theme related stuff in the appropriate place
 */
//modal text
$string['copyright'] = "The present exam and the contents thereof are the property and copyright of the professor(s) who prepared this exam at Concordia University. No part of the present exam may be used for any purpose other than research or teaching purposes at Concordia University. Furthermore, no part of the present exam may be sold, reproduced, republished or re-disseminated in any manner or form without the prior written permission of its owner and copyright holder.";
$string['termsandconditions'] = '<p>You are about to enter into an online exam environment. If you are taking a proctored exam, you must have your Concordia ID card or, failing that, government issued ID readily accessible.</p>
<p>Before you access the exam, you must make sure that you meet all technological and logistical requirements. Read the FAQ about online exams without proctoring or the FAQ about online exams with proctoring, depending on the exam you are about to take. They contain important information and you are strongly encouraged to review that information prior to your exam.</p>
<p>The academic integrity standards applicable to you during this exam are identical to those applicable in an in-person exam. If it is suspected that you did not respect those standards, you may be charged under the Academic Code of Conduct. It is your responsibility to ensure that you remove anything from your exam environment that can be perceived to be unauthorized materials during the exam. Material that you are allowed to use during the exam are mentioned above.</p>
<p>By entering the exam, you represent and warrant that you are the person whose name is associated with the login used in COLE. You affirm that you have had the opportunity to review and that you understand the Academic Code of Conduct.</p>
<p>If you require support during the exam, please call 1-888-202-8615 as soon as possible.</p>';

//aside
$string['support'] = "Support";
$string['technicaldifficultieshdr'] = "Technical difficulty?";
$string['technicaldifficulties'] = "Use the <strong>Exam Support</strong> chat button on the right of your screen to contact COLE support or call the toll free number at the bottom of the page.";
$string['proctorioissueshdr'] = "Proctorio issues?";
$string['proctorioissues'] = "First use the <strong>Exam Support</strong> chat button on the right of your screen to contact COLE support. If they direct you to Proctorio support, click the Proctorio shield icon in your browser (upper right) to access Proctorio live chat.";
$string['questionsinstructorhdr'] = "Questions for your instructor";
$string['questionsinstructor'] = "Use the <strong>Exam Support</strong> chat button on the right of your screen to contact COLE support. They will connect you with your instructor.";
$string['acsdaccomodationshdr'] = "ACSD accommodations";
$string['acsdaccomodations'] = "If you are eligible for extended time, your exam time has been configured and verified by the COLE exam team and ACSD.";
$string['chinaoriranhdr'] = "Writing from China or Iran";
$string['chinaoriran'] = "If your exam is proctored, make sure you are not connected with VPN. VPN is not compatible with proctored exams.";

//checkboxes
$string['instructions'] = "I have read the instructions completely";
$string['copyrightnotice'] = 'I understand the <a href="#" data-toggle="modal" data-target="#copyright-modal">Copyright notice</a> and <a href="#" data-toggle="modal" data-target="#terms-modal">Terms and Conditions</a>';
$string['obligatoryapproval'] = 'You must approve both checkboxes';

//quiz info
$string['testdate']     = 'Date';
$string['testopen']     = 'Exam Opens';
$string['testduration'] = 'Duration';
$string['testnotset']   = 'Not yet available';

$string['supportphone'] = 'Call <strong><a href="tel:+18882028615">1-888-202-8615</a></strong> for support';

/**
 * end strings copied
 */
//review page 
$string['modaltext'] = 'Your exam has been submitted successfully for marking. Please wait for a few seconds, and you will be returned to the exam cover page.';
$string['modaltextextra'] = 'We are having difficulty closing your exam, please notify an invigilator.';

//qr upload page 
$string['qruploadmodaltext'] = 'This section of the exam has finished. In a few seconds you will be redirected to the exam start page where you will scan the QR code and upload your files.';