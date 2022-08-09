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
 * A two column layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

//is the user is a siteadmin makes it true
$isadmin = (is_siteadmin($USER->id)) ? true : false ;
//$isadmin = false;

//$navdrawershow = (user_has_role_assignment($USER->id,5)) ? false : true ;
$navdrawershow = false;

$context = context_course::instance($COURSE->id);

$reflection = new ReflectionClass($context);
$property = $reflection->getProperty('_id');
$property->setAccessible(true);
$contextid = $property->getValue($context);

$ras = get_user_roles($context, $USER->id);
foreach($ras as $role){
    $role_cid = strval($contextid);
    if($role->contextid == $role_cid){
        //assuming student role is default value of 5
        if ($role->roleid != 5){
            $navdrawershow = true;
        }
    }
}

//$navdrawershow = false;

if (isloggedin() && $navdrawershow == true) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;
$showaside = (strpos($bodyattributes,'pagelayout-course ')!==false) ? true : false;
$templatetorender = (strpos($bodyattributes,'page-mod-quiz')!==false) ? 'theme_quizzer/columns2-secure' : 'theme_quizzer/columns2';
$ispagecustom = (strpos($bodyattributes,'page-mod-quiz-attempt')!==false || strpos($bodyattributes,'page-mod-quiz-summary')!==false) ? true : false;
$issummary = (strpos($bodyattributes,'page-mod-quiz-summary')!==false) ? true : false;
$isreview = (strpos($bodyattributes,'page-mod-quiz-review')!==false) ? true : false;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'isadmin' => $isadmin,
    'footerdark' => false,
    'showaside' => $showaside,
    'ispagecustom' => $ispagecustom,
    'issummary' => $issummary,
    'isreview' => $isreview,
    'navdrawershow' => $navdrawershow,
    'shortname' => $OUTPUT->page->course->shortname
];

$nav = $PAGE->flatnav;
$templatecontext['flatnavigation'] = $nav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();
echo $OUTPUT->render_from_template($templatetorender, $templatecontext);

