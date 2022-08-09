<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     theme_quizzer
 * @copyright   2021 Brandon Jimenez <brandon.jimenez@concordia.ca>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


include_once($CFG->dirroot . "/mod/quiz/renderer.php");
include_once($CFG->dirroot . "/question/engine/renderer.php");
include_once($CFG->dirroot . "/lib/outputrenderers.php");

// Import the hybrid question info block.
use local_qrsub\local\qrsub_attempt_info_block;

// Library for QRSub module.
use local_qrsub\local\qrsub;

class theme_quizzer_mod_quiz_renderer extends mod_quiz_renderer {

    /**
     * Output the quiz intro.
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @return string HTML to output.
     */
    public function quiz_intro($quiz, $cm) {
        if (html_is_blank($quiz->intro)) {
            return '';
        }

        return $this->box(format_module_intro('quiz', $quiz, $cm->id), 'generalbox', 'intro');
    }

    /**
     * Output the page information
     *
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @param object $context the quiz context.
     * @param array $messages any access messages that should be described.
     * @return string HTML to output.
     */
    public function view_information($quiz, $cm, $context, $messages) {
        global $CFG;

        $output = '';

        // Print quiz name and description.
        //$output .= $this->heading(format_string($quiz->name));
        $output .= $this->quiz_intro($quiz, $cm);

        // Output any access messages.
        if ($messages) {
            $output .= $this->box($this->access_messages($messages), 'quizinfo');
        }

        // Show number of attempts summary to those who can view reports.
        if (has_capability('mod/quiz:viewreports', $context)) {
            if ($strattemptnum = $this->quiz_attempt_summary_link_to_reports($quiz, $cm,
                    $context)) {
                $output .= html_writer::tag('div', $strattemptnum,
                        array('class' => 'quizattemptcounts'));
            }
        }
        
        return $output;
    }

    /**
     * Generates the data to be sent to the description
     * 
     * @param object $quiz the quiz settings.
     * @return array with the info of the description and options.
     */
    public function quiz_description($quiz) {
        global $DB;
        $sql = "SELECT 'record' AS resultrecord,q.id,
                       COALESCE(czdesc.description,'No description available') as conquizz_description,
                       COALESCE(czopts.options,'No authorized materials') as conquizz_options
                  FROM {quiz} q
             LEFT JOIN {conquizz_descriptions} czdesc ON czdesc.quizid = q.id 
             LEFT JOIN {conquizz_options} czopts ON czopts.quizid = q.id
                 WHERE q.id = :qid";
        $records = $DB->get_records_sql($sql, array('qid'=>$quiz->id));
        return $records;
    }
    

    public function view_confirmation() {
        $output = '';
        $output .= $this->render_from_template('theme_quizzer/checkboxes', '');
        return $output;
    }

    public function get_role($context){
        global $USER;
        $capability = has_capability('moodle/course:create',$context,$USER->id);
        $capability2 = has_capability('mod/quiz:grade',$context,$USER->id);
        return ($capability == 1 || $capability2 == 1)? "non-student" : "student";
    }

    public function lp_hidden_fields($context){
        global $USER;
        $output = '';

        $output .= html_writer::start_tag('div', array('id' =>'lpdata', 'class'=>'d-none'));
        $output .= html_writer::tag('div',$USER->username, array('id'=>'lpuser'));
        $output .= html_writer::tag('div',$USER->idnumber, array('id'=>'lpidn'));
        $output .= html_writer::tag('div',$USER->aim, array('id'=>'lpaim'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'lpuserrole',
                'value' => $this->get_role($context), 'id' => 'lpuserrole'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Generates the view page
     *
     * @param int $course The id of the course
     * @param array $quiz Array conting quiz data
     * @param int $cm Course Module ID
     * @param int $context The page context ID
     * @param array $infomessages information about this quiz
     * @param mod_quiz_view_object $viewobj
     * @param string $buttontext text for the start/continue attempt button, if
     *      it should be shown.
     * @param array $infomessages further information about why the student cannot
     *      attempt this quiz now, if appicable this quiz
     */
    public function view_page($course, $quiz, $cm, $context, $viewobj) {
        global $USER;
        $output = '';
        $results = $this->quiz_description($quiz);

        //variables
        $timeopen  = ($quiz->timeopen != 0) ? date('D, F d,Y', $quiz->timeopen):get_string('testnotset', 'quizaccess_conquizzer');
        $houropen  = ($quiz->timeopen != 0) ? date('h:i a', $quiz->timeopen):get_string('testnotset', 'quizaccess_conquizzer');
        $timelimit = ($quiz->timelimit != 0) 
            ? ( ($quiz->timelimit >= 3600 ) 
                ? floor($quiz->timelimit / 3600)." hr. ".(($quiz->timelimit / 60) % 60)." min. " 
                : (($quiz->timelimit / 60) % 60)." min. ")
            : get_string('testnotset', 'quizaccess_conquizzer');

        //modifications to conform to proposed mock-up
        $output .= $this->heading(format_string($quiz->name));

        $output .= html_writer::start_tag('div', array('class'=>'row', 'id'=>'upper-content'));
        
        $output .= html_writer::start_tag('div', array('id' =>'concordia-left', 'class'=>'col-6'));
        //$output .= html_writer::start_tag('div', array('id' =>'concordia-left', 'class'=>'col-9 row'));
        //$output .= html_writer::start_tag('div', array('id' =>'support-accordion', 'class'=>'col-3'));
        //$output .= $this->render_from_template('theme_quizzer/help', '');
        //$output .= html_writer::end_tag('div');
        //$output .= html_writer::start_tag('div', array('id' =>'exam-info', 'class'=>'col-9'));
        $output .= $this->render_from_template('theme_quizzer/description', $this->quiz_description($quiz));
        //$output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('id' =>'concordia-right', 'class'=>'col-6'));
        $output .= html_writer::tag('span', get_string('examinfo', 'quizaccess_conquizzer'), array('class'=>'h3'));
        $output .= html_writer::tag('hr','', array('class'=>'underlined'));

        //original call to functions
        
        //array_unshift($viewobj->infomessages,html_writer::tag('span', get_string('testdate', 'quizaccess_conquizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $timeopen, array('class'=>'span-content')),html_writer::tag('span', get_string('testopen', 'quizaccess_conquizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $houropen, array('class'=>'span-content')),html_writer::tag('span', get_string('testduration', 'quizaccess_conquizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $timelimit, array('class'=>'span-content')));
        $viewobj->infomessages = ($quiz->timeopen != 0)  
                                                ? array(html_writer::tag('span', get_string('testdate', 'quizaccess_conquizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $timeopen, array('class'=>'span-content')),html_writer::tag('span', get_string('testopen', 'quizaccess_conquizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $houropen, array('class'=>'span-content')),html_writer::tag('span', get_string('testduration', 'quizaccess_conquizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $timelimit, array('class'=>'span-content')))
                                                : array(html_writer::tag('span', get_string('testduration', 'quizaccess_conquizzer'),array('class'=>'h6 strong centered red')).html_writer::tag('span', $timelimit, array('class'=>'span-content')));

        //check to add attempts made
        $attemps = ($quiz->attempts == 0)?'Unlimited':$quiz->attempts;
        if ($this->get_role($context)!='student'){
            array_push($viewobj->infomessages,html_writer::tag('span', get_string('testattempts', 'theme_quizzer'), array('class'=>'h6 strong centered red')).html_writer::tag('span', $attemps, array('class'=>'span-content'))); 
        } 
        $output .= $this->view_information($quiz, $cm, $context, $viewobj->infomessages);
        //$output .= $this->view_table($quiz, $context, $viewobj);
        $output .= $this->view_result_info($quiz, $context, $cm, $viewobj);
        //not original call
        $output .= $this->view_confirmation();
        //end not original call
        $output .= $this->box($this->view_page_buttons($viewobj), 'quizattempt');
        //end original calls

        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        $output .= $this->view_table($quiz, $context, $viewobj);

        /*
        * here goes the hybrid question code provided by
        * Nicolas Dulpe
        */
        // We need at least one attempt object.
        // Display the QR Code if there is hybrid question in the exam.
        if (isset($viewobj->attemptobjs[0])) {
            $qrsub = new qrsub();
            $output .= $qrsub->display_qrcode($viewobj->attemptobjs[0], $cm);
        }
        /*end hybrid question code */

        $output .= $this->render_from_template('theme_quizzer/copy_modal', '');
        $output .= $this->render_from_template('theme_quizzer/terms_modal', '');
        $output .= $this->lp_hidden_fields($context);
        
        $output .= $this->page->requires->js_call_amd('theme_quizzer/landing','init');
        $output .= $this->page->requires->js_call_amd('theme_quizzer/landing','qrchanges');
        $output .= $this->page->requires->js_call_amd('theme_quizzer/landing','rearranger');
        
        return $output;
    }

    /**
     * Attempt Page
     *
     * @param quiz_attempt $attemptobj Instance of quiz_attempt
     * @param int $page Current page number
     * @param quiz_access_manager $accessmanager Instance of quiz_access_manager
     * @param array $messages An array of messages
     * @param array $slots Contains an array of integers that relate to questions
     * @param int $id The ID of an attempt
     * @param int $nextpage The number of the next page
     */
    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id,
            $nextpage) {
        $output = '';
        $output .= $this->header();
        $output .= $this->quiz_notices($messages);
        $output .= $this->header_questions_attempted($attemptobj, false,true);
        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $output .= $this->footer();
        return $output;
    }
    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel) {
        global $OUTPUT,$USER;
        $extraclasses = [];
        $bodyattributes = $OUTPUT->body_attributes($extraclasses);

        $output = '';
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
                    array('id' => 'user-picture', 'class' => 'clearfix'));
        }
        $output .= $panel->render_before_button_bits($this);
        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $output .= $this->render($button);
        }
        $output .= html_writer::end_tag('div');

        //the color key segment
        $bodyattributes = $OUTPUT->body_attributes($extraclasses);

        $reflection = new ReflectionClass($panel);
        $property = $reflection->getProperty('attemptobj');
        $property->setAccessible(true);
        $attemptobj = $property->getValue($panel);
        $qrsub = new qrsub();
        $has_hybrid = $qrsub->has_hybrid_question($attemptobj);

        if ($has_hybrid) {
            $search_keys = array('answered','unsure','unanswered','invalidanswerhybrid');
        }else{
            $search_keys = array('answered','unsure','unanswered','invalidanswer');
        }
        if (strpos($bodyattributes,'page-mod-quiz-review')!==false){
            $search_keys = array('correct','partiallycorrect','incorrect','notanswered','gradingrequired');
        }
        $key_strings = get_strings($search_keys, 'theme_quizzer');
        $array_keys = json_decode(json_encode($key_strings), true);
        $output .= html_writer::start_tag('div', array('class' => "question_key"));
        $output .= html_writer::tag('span', get_string('question_key', 'theme_quizzer'));
        $output .= html_writer::start_tag('ul');
        foreach ( $array_keys as $key => $value )
        {
            $output .= html_writer::start_tag('li');
            $output .= html_writer::tag('span', $value, array('class' => "$key"));
            $output .= html_writer::end_tag('li');
        }
        
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_tag('div');

        if (strpos($bodyattributes,'quiz-attempt')!==false) {
            $output .= $this->last_autosave();
        } 
        $output .= html_writer::tag('div', $panel->render_end_bits($this),
            array('class' => 'othernav'));
        
        $this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
                quiz_get_js_module());

        return $output;
    }

    /**
     * Display a quiz navigation button.
     *
     * @param quiz_nav_question_button $button
     * @return string HTML fragment.
     */
    protected function render_quiz_nav_question_button(quiz_nav_question_button $button) {
        $classes = array('qnbutton', $button->stateclass, $button->navmethod, 'btn');
        $extrainfo = array();
        $unsureclasses = array ('unsure');

        if ($button->currentpage) {
            $classes[] = 'thispage';
            $extrainfo[] = get_string('onthispage', 'quiz');
        }

        // Flagged?
        if ($button->flagged) {
            //$classes[] = 'flagged';
            $flaglabel = get_string('flagged', 'question');
            $unsureclasses[]='flagged';
        } else {
            $flaglabel = '';
        }
        $extrainfo[] = html_writer::tag('span', $flaglabel, array('class' => 'flagstate'));

        if (is_numeric($button->number)) {
            $qnostring = 'questionnonav';
        } else {
            $qnostring = 'questionnonavinfo';
        }
        $a = new stdClass();
        $a->number = $button->number;
        $a->attributes = implode(' ', $extrainfo);
        $tagcontents =  html_writer::tag('span', '', array('class' => 'thispageholder')) .
                        html_writer::tag('span', '', array('class' => 'trafficlight')) .
                        html_writer::tag('span', get_string($qnostring, 'quiz', $a) , array('class' => 'buttonnumber')) .
                        html_writer::tag('span', '', array('class' => implode(' ', $unsureclasses)));
        $tagattributes = array('class' => implode(' ', $classes), 'id' => $button->id,
                                  'title' => $button->statestring, 'data-quiz-page' => $button->page);

        if ($button->url) {
            return html_writer::link($button->url, $tagcontents, $tagattributes);
        } else {
            return html_writer::tag('span', $tagcontents, $tagattributes);
        }
    }

    protected function render_quiz_nav_section_heading(quiz_nav_section_heading $heading) {
        return $this->heading($heading->heading, 6, 'mod_quiz-section-heading');
    }

    /**
     * Display the prev/next buttons that go at the bottom of each page of the attempt.
     *
     * @param int $page the page number. Starts at 0 for the first page.
     * @param bool $lastpage is this the last page in the quiz?
     * @param string $navmethod Optional quiz attribute, 'free' (default) or 'sequential'
     * @return string HTML fragment.
     */
    protected function attempt_navigation_buttons_custom($page, $lastpage, $navmethod = 'free',$attemptid,$cmid,$num_pages,$pagesshow=5) {
        global $DB,$PAGE;
        $quizid = $DB->get_field('quiz_attempts','quiz',array('id' => $attemptid));
        
        $baseurl = strtok($PAGE->url, "?");

        $output = '';

        $prevlabel = get_string('navigateprevious', 'quiz');
        $nextlabel = get_string('navigatenext', 'quiz');
        
        $pagesshow = ($num_pages < 5) ?$num_pages:$pagesshow;
        $changeperc = floor($pagesshow*0.7);
        
        $previousclasses = ($page == 0)? "disabled pe-none":'';
        $nextclasses = ($lastpage)? "disabled pe-none d-none":'';
        $last = ($lastpage==1)? "true":'false';
        $output .= html_writer::start_tag('div', array('id' => 'attempt_navigation_buttons'));
        $output .= html_writer::start_tag('div', array('class' => 'main_buttons'));
        if ($page >0 ){
            $output .= html_writer::empty_tag('input',array('type' => 'submit','name' => 'previous','value' => $prevlabel,'class' => $previousclasses));
        }

        $start_level = 0;
        if (($page >= 0)&&($page < $changeperc)||($num_pages==1)){
            $start_level = 1; 
        }
        elseif (($page >= $changeperc)&&($page < $num_pages - $changeperc)){
            $start_level = $page - 1; 
        }
        elseif($page >= $num_pages - $changeperc){
            $start_level = $num_pages - ($pagesshow-1);
        }
        for ($i=1;$i<=$pagesshow;$i++) {
            
            $options = array_merge(
                ['attempt' => $attemptid, 'cmid' => $cmid],
                ($start_level > 1 ? ["page" => $start_level-1] : [])
            );
            $output .= html_writer::link(
                new moodle_url($baseurl, $options),
                $start_level,
                array(  "class" => "qnbutton",
                        "data-current-page" => ($start_level == $page+1) ? "true" : "false"
                    )
                );
            $start_level++;
        }
        
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
                'value' => $nextlabel,'class'=>$nextclasses, 'data-last-page'=>$last));

        $output .= html_writer::end_tag('div');     
        $output .= html_writer::link(
            new moodle_url($baseurl, array('attempt' => $attemptid, 'cmid' => $cmid)), 
            get_string('endtest', 'quiz'),
            array("class"=>'endtestlink', "role"=>"button")
        ); 
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Ouputs the form for making an attempt
     *
     * @param quiz_attempt $attemptobj
     * @param int $page Current page number
     * @param array $slots Array of integers relating to questions
     * @param int $id ID of the attempt
     * @param int $nextpage Next page number
     */
    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {
        $output = '';

        // Start the form.
        $output .= html_writer::start_tag('form',
                array('action' => new moodle_url($attemptobj->processattempt_url(),
                array('cmid' => $attemptobj->get_cmid())), 'method' => 'post',
                'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
                'id' => 'responseform'));
        $output .= html_writer::start_tag('div');
        // Print all the questions.
        foreach ($slots as $slot) {
            $type = $attemptobj->get_question_type_name($slot);
            $question_types = array('multichoice');
            if (in_array($type, $question_types)){
                $output .= $this->cleanup_tags($attemptobj,$slot);
            } 
            $output .= $attemptobj->render_question(
                                                $slot, 
                                                false, 
                                                $this,
                                                $attemptobj->attempt_url($slot, $page), 
                                                $this);
        }

        $navmethod = $attemptobj->get_quiz()->navmethod;
        $output .= $this->attempt_navigation_buttons_custom($page, $attemptobj->is_last_page($page), $navmethod,$attemptobj->get_attemptid(),$attemptobj->get_cmid(),$attemptobj->get_num_pages());

        // Some hidden fields to trach what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
                'value' => $attemptobj->get_attemptid()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'thispage',
                'value' => $page, 'id' => 'followingpage'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'nextpage',
                'value' => $nextpage));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'timeup',
                'value' => '0', 'id' => 'timeup'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                'value' => sesskey()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'scrollpos',
                'value' => '', 'id' => 'scrollpos'));

        // Add a hidden field with questionids. Do this at the end of the form, so
        // if you navigate before the form has finished loading, it does not wipe all
        // the student's answers.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots',
                'value' => implode(',', $attemptobj->get_active_slots($page))));

        // Finish the form.
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        $output .= $this->connection_warning();

        $output .= $this->page->requires->js_call_amd('theme_quizzer/attempt','init');

        return $output;
    }

    /**
     * Cleanup tags at the beginning or end of a given string
     *
     * @param quiz_attempt $attemptobj
     * @param int $slot Array of integers relating to questions
     * @return string HTML Fragment
     */
    public function cleanup_tags($attemptobj,$slot) {
        $ans = $attemptobj->get_question_attempt($slot)->get_question(false);
        foreach ($ans->answers as $singleanswer) {
            $answercleaned = preg_replace("/^(<p.*?>)/i", "", $singleanswer->answer);
            $answercleaned = preg_replace("/(<\/p>)$/i", "", $answercleaned);
            $answercleaned = preg_replace("/(<br>)$/i", "", $answercleaned);
            $singleanswer->answer = $answercleaned;
        }
    }

    /**
     * Get an HTML string with the last time an attempt was saved.
     *
     * @return string HTML Fragment
     */
    public function last_autosave() {
        global $DB;

        $attemptid = optional_param('attempt', false, PARAM_INT);

        $sql = "SELECT MAX(qat.timemodified) AS lastsaved
                FROM {quiz_attempts} qa
                INNER JOIN mdl_question_usages qu ON qa.uniqueid = qu.id
                INNER JOIN mdl_question_attempts qat ON qat.questionusageid = qu.id
                WHERE qa.id = :attemptid";

        $lastsave = $DB->get_field_sql($sql, ['attemptid' => $attemptid]);

        $lastsavestr = $lastsave == 0 ? get_string('never') : '';

        return html_writer::span(html_writer::span(get_string('lastautosave', 'theme_quizzer'),"strong h6") .html_writer::span($lastsavestr, 'lastsaved', ['data-lastsaved' => $lastsave]),"conu-lastautosave");
    }

    /*
     * Summary Page
     */
    /**
     * Create the summary page
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_page($attemptobj, $displayoptions) {
        global $CFG,$PAGE;

        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        //$output .= $this->heading(get_string('summaryofattempt', 'quiz'), 3);
        $output .= $this->filter_panel($attemptobj, $displayoptions);
        $output .= html_writer::start_tag('div', array('class' => "summarycontent")); 
        //$output .= $this->header_questions_attempted($attemptobj, $displayoptions);
        $output .= $this->header_questions_attempted($attemptobj, false);
        $output .= $this->summary_table($attemptobj, $displayoptions);
        $output .= $this->summary_page_controls($attemptobj);
        $output .= html_writer::end_tag('div');
        $output .= $this->page->requires->js_call_amd('theme_quizzer/summary','init');
        //$output .= $this->page->requires->js_call_amd('theme_quizzer/summary','modalSummary');
        $PAGE->requires->js(new moodle_url('/theme/quizzer/javascript/modal_summary.js'));
        $output .= $this->footer();
        $templatecontext = [
            'quiz_name' => $attemptobj->get_quiz_name(),
            'isreview' => false
        ];
        /*
        * new logic to be added to contemplate scenarios with the hybrid questions
        *  The checkmark page:
        *  1 - No hybrid question: no change
        *  2 - Hybrid question: display a message specifying: 
            the student is done with the first part of the exam, 
            he will be redirected to the attempt start page 
            where he will scan the QR Code and upload his file.
        */
        $qrsub = new qrsub();
        $hashybrid = $qrsub->has_hybrid_question($attemptobj);
        if ($hashybrid) {
            $output .= $this->render_from_template('theme_quizzer/qr_upload', $templatecontext);
        } else {
            $output .= $this->render_from_template('theme_quizzer/review', $templatecontext);
        }
        //end new logic

        return $output;
    }

    public function filter_panel($attemptobj, $displayoptions){
        $output = '';
        $slots = $attemptobj->get_slots();
        //$filterall = count($slots);
        $filterall = 0;
        $answered=0;
        $unsure=0;
        $unanswered=0;
        $invalidanswer=0;
        /*
        $type = $attemptobj->get_question_type_name($slot);
            if($type!='description'){
                $total++;
            }
        */
        $search_keys = array('filterall','answered','invalidanswer','unanswered','unsure');
        $output .= html_writer::start_tag('div', array('class' => "summarysortby"));
        $output .= html_writer::div(get_string('sortby', 'theme_quizzer'),'sort-title');
        foreach ($slots as $slot) {
            $type = $attemptobj->get_question_type_name($slot);
            if($type!='description'){
                $filterall++;
            }
            $state = $attemptobj->get_question_state_class($slot, $displayoptions->correctness);
            switch ($state) {
                case "notyetanswered":
                    $unanswered++;
                    break;
                case "invalidanswer":
                    $invalidanswer++;
                    break;
                case "answersaved":
                    if($type!='description'){
                        $answered++;
                    }
                    break;
                default:
                    echo "House was here!";
            }
            if ($attemptobj->is_question_flagged($slot)) {
                $unsure++;
            }

        }
        foreach ($search_keys as $key) {
            
            $qrsub = new qrsub();
            $has_hybrid = $qrsub->has_hybrid_question($attemptobj);

            if ($key=='unsure'){
                $outtext = get_string('unsureshort', 'theme_quizzer');
            }
            else {
                if ($has_hybrid && $key=='invalidanswer') {
                    $outtext = get_string('invalidsummaryhybridbutton', 'theme_quizzer');
                }
                else{
                    $outtext = get_string($key, 'theme_quizzer');
                }
            }
            $enabled = ($key == 'filterall')?'enabled':'disabled';
            $attributes = array('class' => "sortbtn $key", 'name' => "$key", 'type' => "button", 'data-filter' => $enabled);
            //$contents = html_writer::span(${$key}).html_writer::span(get_string(($key=='unsure')?'unsureshort':$key, 'theme_quizzer'));
            $contents = html_writer::span(${$key}).html_writer::span($outtext);
            $output .= html_writer::tag('button', $contents, $attributes);
        }
        $output .= html_writer::end_tag('div');
        return $output;

    } 
    public function header_questions_attempted($attemptobj,$display=false,$bar = false) {
        $output = '';

        $slots = $attemptobj->get_slots();
        $attempted = 0;
        $total = 0;
        foreach ($slots as $slot) {
            $state = $attemptobj->get_question_state_class($slot, $display);
            $type = $attemptobj->get_question_type_name($slot);
            if($type=='hybrid' && ($state=='answersaved'||$state=='invalidanswer')){
                $attempted++;
            } 
            if($state=='answersaved' && $type!='description'){
                $attempted++;
            }
            if($type!='description'){
                $total++;
            }
        }
        //$total = count($slots);
        $contents = get_string('questionsatt', 'theme_quizzer').$attempted."/".$total;
        $classes = array('class' => 'summarymarks');
        //$output .= html_writer::tag('div', $contents, $classes);
        if ($bar == true){
            $percentage = ($attempted/$total)*100;
            $output .= html_writer::start_tag('div', array('class' => "container mb-4 mx-w-inh"));
            $output .= html_writer::tag('div', $contents, $classes);
            $output .= html_writer::start_tag('div', array('class' => "progress"));
            //$output .= html_writer::start_tag('div', array('class' => "progress-bar", 'role' => "progressbar",'aria-valuenow' => $percentage,'aria-valuemin' => 0,'aria-valuemax' => 100,'style' => "width:$percentage%"));
            $output .= html_writer::start_tag('div', array('class' => "progress-bar", 'role' => "progressbar",'aria-valuenow' => $attempted,'aria-valuemin' => 0,'aria-valuemax' => $total,'style' => "width:$percentage%"));
            $output .= html_writer::tag('span', $percentage." completed", array('class' => "sr-only"));
            $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        }
        else{
            $output .= html_writer::tag('div', $contents, $classes);
        }
        return $output;
    }

    /**
     * Creates any controls a the page should have.
     *
     * @param quiz_attempt $attemptobj
     */
    public function summary_page_controls($attemptobj) {
        $output = '';

        $urltogo = new moodle_url('/course/view.php', array('id' => $attemptobj->get_courseid()));
        // Finish attempt button.
        $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'cmid' => $attemptobj->get_cmid(),
            'sesskey' => sesskey(),
            'returnto' => 'url',
            'returnurl' => $urltogo,
        );

        $button = new single_button(
                new moodle_url($attemptobj->processattempt_url(), $options),
                get_string('submitallandfinish', 'quiz'),'post',false,array('data-id'=>'modal-shower'));
        $button->id = 'responseform';
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
                    get_string('submitallandfinish', 'quiz')));
        }

        $duedate = $attemptobj->get_due_date();
        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));

        } /*else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
        }*/

        $button_return = '';
        // Return to place button.
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button_return = new single_button(
                    new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                    get_string('returnattempt', 'quiz'));
            /*$output .= $this->container($this->container($this->render($button_return),
                    'return'), 'summarycontrols');*/
        }

        $output .= $this->countdown_timer($attemptobj, time());
        /*$output .= $this->container($message . $this->container(
                $this->render($button), 'controls'), 'submitbtns mdl-align');*/
        $output .= $this->container(
                        $this->container($message,'message').
                        $this->container($this->render($button_return),'return').
                        $this->container($this->render($button), 'submit'), 
                        'summarycontrols');

        return $output;
    }

    /**
     * Generates the table of summarydata
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_table($attemptobj, $displayoptions) {
        global $USER;
        
        //global $USER,$DB;
        // Prepare the summary table header.
        $table = new html_table();
        //$table->attributes['class'] = 'generaltable quizsummaryofattempt boxaligncenter';
        $table->attributes['class'] = 'flexible';
        $table->head = array(
                            get_string('questionno', 'theme_quizzer'),
                            get_string('question', 'quiz'), 
                            get_string('status', 'quiz')
                        );
        //$table->align = array('left', 'left');
        $table->size = array('', '');
        $markscolumn = $displayoptions->marks = question_display_options::MARK_AND_MAX;
        if ($markscolumn) {
            $table->head[] = get_string('marks', 'quiz');
            //$table->align[] = 'left';
            $table->size[] = '';
        }
        //$tablewidth = count($table->align);
        $table->data = array();
        
        //$cell->width[1]= '60%';

        // Get the summary info for each question.
        $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
            // Add a section headings if we need one here.
            $heading = $attemptobj->get_heading_before_slot($slot);
            if ($heading) {
                $cell = new html_table_cell(format_string($heading));
                $cell->header = true;
                //$cell->colspan = $tablewidth;
                $table->data[] = array($cell);
                $table->rowclasses[] = 'quizsummaryheading';
            }

            // Don't display information items.
            if (!$attemptobj->is_real_question($slot)) {
                continue;
            }
            // Real question, show it.
            $flag = '';
            $flagged = '';
            if ($attemptobj->is_question_flagged($slot)) {
                $flagged = 'flagged';
                // Quiz has custom JS manipulating these image tags - so we can't use the pix_icon method here.
                $flag = html_writer::empty_tag('img', array('src' => $this->image_url('i/flagged'),
                        'alt' => get_string('flagged', 'question'), 'class' => 'questionflag icon-post'));
            }
            //if ($attemptobj->can_navigate_to($slot) && $checkstu == 'else') {
            if ($attemptobj->can_navigate_to($slot)) {
                $row = array(
                            html_writer::link(  $attemptobj->attempt_url($slot),
                                                $attemptobj->get_question_number($slot)),
                            html_writer::link(  $attemptobj->attempt_url($slot),
                                                html_writer::tag(
                                                                'div',
                                                                strip_tags($attemptobj->get_question_attempt($slot)->get_question($slot,false)->questiontext),
                                                                array('class'=>'questiontext-container'))),
                            html_writer::link(  $attemptobj->attempt_url($slot),
                            $this->convert_status($attemptobj->get_question_status($slot, $displayoptions->correctness),$flagged, $attemptobj->get_question_type_name($slot)))
                        );
            } else {
                $row = array(
                            $attemptobj->get_question_number($slot) . $flag,
                            $attemptobj->get_question_attempt($slot)->get_question($slot,false)->questiontext,
                            $this->convert_status($attemptobj->get_question_status($slot, $displayoptions->correctness),$flagged)
                        );
            }
            if ($markscolumn) {
                //$row[] = $attemptobj->get_question_mark($slot);
                $row[] = html_writer::link( $attemptobj->attempt_url($slot),
                                            $attemptobj->get_question_attempt($slot)->get_max_mark());
            }
            $table->data[] = $row;
            $table->rowclasses[] = 'answerrow quizsummary' . $slot . ' ' . $attemptobj->get_question_state_class(
                    $slot, $displayoptions->correctness).' '.$flagged;

        }

        // Print the summary table.
        $output = html_writer::nonempty_tag(
                                            'div', 
                                            html_writer::tag(
                                                'div',
                                                html_writer::tag(
                                                    'div',
                                                    html_writer::tag(
                                                        'div',
                                                        html_writer::table($table),
                                                        array('class'=>'no-overflow')        
                                                    ),
                                                    array('class'=>'summarytable')        
                                                )
                                            ), 
                                            array('class'=>'summarycontent')
                                        );
        $output = html_writer::nonempty_tag('div',
                                                html_writer::tag(
                                                    'div',
                                                    html_writer::tag(
                                                        'div',
                                                        html_writer::table($table),
                                                        array('class'=>'no-overflow')        
                                                    ),
                                                    array('class'=>'summarytable')        
                                                )
                                        );

        return $output;
    }

    public function convert_status($status,$flagged,$type='') {
        $converted = '';
        switch ($status) {
            case "Answer saved":
                $converted .=  get_string('answered', 'theme_quizzer');
                $class = 'answered';
                break;
            case "Not yet answered":
                $converted .=  get_string('unanswered', 'theme_quizzer');
                $class = 'unanswered';
                break;
            case "Incomplete answer":
                if($type == 'hybrid'){
                    $converted .=  get_string('invalidsummaryhybrid', 'theme_quizzer');
                }else{
                    $converted .=  get_string('invalidanswer', 'theme_quizzer');
                }
                $class = 'invalidanswer';
                break;
        }
        $flagged = ($flagged == 'flagged')?html_writer::nonempty_tag('span',get_string('unsureshort', 'theme_quizzer'), array('class'=>$flagged)).' / ':'';
        $output = $flagged.html_writer::nonempty_tag('span', $converted, array('class'=>$class));
        return $output;
    }

    /**
     * Builds the review page
     *
     * @param quiz_attempt $attemptobj an instance of quiz_attempt.
     * @param array $slots an array of intgers relating to questions.
     * @param int $page the current page number
     * @param bool $showall whether to show entire attempt on one page.
     * @param bool $lastpage if true the current page is the last page.
     * @param mod_quiz_display_options $displayoptions instance of mod_quiz_display_options.
     * @param array $summarydata contains all table data
     * @return $output containing html data.
     */
    public function review_page(quiz_attempt $attemptobj, $slots, $page, $showall,
                                $lastpage, mod_quiz_display_options $displayoptions,
                                $summarydata) {
        global $OUTPUT;
                            
        $output = '';
        $output .= $this->header();

        /*
        * new logic to be added to contemplate scenarios with the hybrid questions
        *  The checkmark page:
        *  1 - No hybrid question: redirect to course main page
        *  2 - Hybrid question: redirect to quiz main page
        */
        $qrsub = new qrsub();
        $hashybrid = $qrsub->has_hybrid_question($attemptobj);
        if ($hashybrid) {
            $urltogo = new moodle_url('/mod/quiz/view.php', array('id' => $attemptobj->get_cmid()));
        } else {
            $urltogo = new moodle_url('/course/view.php', array('id' => $attemptobj->get_courseid()));
        }
        //end new logic

        /*$templatecontext = [
            'quiz_name' => $attemptobj->get_quiz_name(),
            'isreview' => true,
            'urltogo' => $urltogo
        ];*/
        
        $roleuser = $this->get_role($attemptobj->get_quizobj()->get_context());
        //if it's a student, redirect
        if ($roleuser == 'student'){
            //$output .= $this->render_from_template('theme_quizzer/review', $templatecontext);
            $when = quiz_attempt_state($attemptobj->get_quiz(), $attemptobj->get_attempt());
            $t=time();
            $closingtime = $attemptobj->get_quiz()->timeclose;
            //if the attempt is still open but the student has the capability to see the review set by
            //the teacher, still, it should only be visible after the quiz is closed
            if ($closingtime && 
                $t<$closingtime && 
                $attemptobj->get_quiz()->reviewattempt & mod_quiz_display_options::AFTER_CLOSE){
                $output .= get_string('noreviewuntil', 'quiz',userdate($closingtime));
            }
            //now we check if the attempt is indeed closed
            else if($closingtime && 
                    $t>$closingtime && 
                    $attemptobj->get_quiz()->reviewattempt & mod_quiz_display_options::AFTER_CLOSE){
                $output .= $this->review_summary_table($summarydata, $page);
                $output .= $this->review_form($page, $showall, $displayoptions,
                        $this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
                        $attemptobj);

                $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
                $output .= $this->footer();
            }
            else {
                sleep(10);
                redirect($urltogo);
            }
        }
        else {
            $output .= $this->review_summary_table($summarydata, $page);
            $output .= $this->review_form($page, $showall, $displayoptions,
                    $this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
                    $attemptobj);

            $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
            $output .= $this->footer();
        }
        return $output;
    }

    /**
     * Generate a brief textual desciption of the current state of an attempt.
     * @param quiz_attempt $attemptobj the attempt
     * @param int $timenow the time to use as 'now'.
     * @return string the appropriate lang string to describe the state.
     */
    public function attempt_state($attemptobj) {
        global $DB, $USER;
        switch ($attemptobj->get_state()) {
            case quiz_attempt::IN_PROGRESS:

                ///////////////////////////////////////////////////////
                // QRMOD-11 - POC a file sub in a Hybrid question type.
                //
                // Contains the question and their completion status.
                $questionstatus = $status = '';

                // Load the questions in the attempt object.
                $attemptobj->load_questions();

                // Get the hybrid question and their status.
                $hybridinfo = new qrsub_attempt_info_block($attemptobj);
                $hybrids = $hybridinfo->get_questions();

                // Find if the exam is proctored.
                list($is_proctored, $upload_exam) = qrsub::is_exam_protored($attemptobj);

                // Display the question status only if we have hybrid question.
                if (count($hybrids) > 0 && !$is_proctored) {

                    $this->page->requires->js_call_amd(
                        'local_qrsub/attempt_status_unproctored',
                        'init',
                        array($attemptobj->get_attemptid())
                    );

                    // Exam completion status.
                    $status .= html_writer::tag(
                        'span',
                        new lang_string('hybrid_upload', 'local_qrsub'),
                        array('id' => 'qrsub_attempt_status')
                    );

                    // Display the question.
                    foreach ($hybrids as $hybrid) {
                        $questionstatus .= html_writer::tag(
                            'div',
                            $hybrid['name'] . ' ' . $hybrid['complete'],
                            array('class' => $hybrid['complete_css_class'])
                        );
                    }

                    // Contains the hybrid questions status.
                    $status .= html_writer::tag(
                        'div',
                        $questionstatus,
                        array('id' => 'hybrid_status')
                    );
                    // QRMOD-11
                } else {
                    $status .= new lang_string('stateinprogress', 'quiz');
                }

                return $status;

            case quiz_attempt::OVERDUE:
                return get_string('stateoverdue', 'quiz') . html_writer::tag(
                    'span',
                    get_string(
                        'stateoverduedetails',
                        'quiz',
                        userdate($attemptobj->get_due_date())
                    ),
                    array('class' => 'statedetails')
                );

            case quiz_attempt::FINISHED:

                ///////////////////////////////////////////////////////
                // QRMOD-11 - POC a file sub in a Hybrid question type.
                //
                // Contains the HTML to output.
                $status = '';

                // Load the questions in the attempt object.
                $attemptobj->load_questions();

                $qrsub = new qrsub();
                $has_hybrid = $qrsub->has_hybrid_question($attemptobj);

                if ($has_hybrid) {

                    if (!$qrsub->question_attempt_has_uploaded_file($attemptobj)) {

                        // Check if the attempt is proctored or not.
                        list($is_proctored, $upload_exam) = qrsub::is_exam_protored($attemptobj);
                        if ($is_proctored) {
                            ///////////////////////////////////////////////////////
                            // QRMOOD-51 - As an IT, I want the exam refresh rate to be x sec
                            //
                            // Get the exam's refresh rate settings.
                            $qrsub_setting = get_config('local_qrsub');
                            if ($qrsub_setting === false) {
                                $exam_refresh_rate = 2000;
                            } else {
                                $exam_refresh_rate = intval($qrsub_setting->exam_refresh_rate) * 1000;
                            }
                            // QRMOOD-51

                            $this->page->requires->js_call_amd(
                                'local_qrsub/attempt_status_proctored',
                                'init',
                                array($upload_exam, $exam_refresh_rate)
                            );
                        } else {
                            $this->page->requires->js_call_amd(
                                'local_qrsub/attempt_status_unproctored',
                                'init',
                                array($attemptobj->get_attemptid())
                            );
                        }

                        // Exam completion status.
                        $status .= html_writer::tag(
                            'span',
                            new lang_string('non_hybrid_finished', 'local_qrsub'),
                            array('id' => 'qrsub_attempt_status')
                        );

                        // First attempt completion date.
                        $status .= html_writer::tag(
                            'span',
                            get_string(
                                'statefinisheddetails',
                                'quiz',
                                userdate($attemptobj->get_submitted_date())
                            ),
                            array('class' => 'statedetails')
                        );

                        // Holds the questions status.
                        $status .= html_writer::tag(
                            'div',
                            new lang_string('no_attempt_yet', 'local_qrsub'),
                            array('id' => 'hybrid_status')
                        );
                    } else {
                        // Exam completion status.
                        $status .= html_writer::tag(
                            'span',
                            new lang_string('exam_finished', 'local_qrsub'),
                            array('id' => 'qrsub_attempt_status')
                        );

                        // First attempt completion date.
                        $status .= html_writer::tag(
                            'span',
                            get_string(
                                'statefinisheddetails',
                                'quiz',
                                userdate($attemptobj->get_submitted_date())
                            ),
                            array('class' => 'statedetails')
                        );
                    }
                // QRMOD-11
                } else {
                    // Moodle's default below.
                    $status .= get_string('statefinished', 'quiz') . html_writer::tag(
                        'span',
                        get_string(
                            'statefinisheddetails',
                            'quiz',
                            userdate($attemptobj->get_submitted_date())
                        ),
                        array('class' => 'statedetails')
                    );
                }

                return $status;

            case quiz_attempt::ABANDONED:
                return get_string('stateabandoned', 'quiz');
        }
    }
}

class theme_quizzer_core_question_renderer extends core_question_renderer {

    /**
     * Generate the information bit of the question display that contains the
     * metadata like the question number, current state, and mark.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function info(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
            qtype_renderer $qtoutput, question_display_options $options, $number) {
        global $OUTPUT;
        $output = '';
        $output .= $this->number_custom($number, $qa);

        $extraclasses = [];
        $bodyattributes = $OUTPUT->body_attributes($extraclasses);
        /**
         * two conditions 
         * a. the exam is closed and
         * b. teacher enabled review after exam closed
         */
        if (!empty($options->editquestionparams)) {
            $output .= $this->status($qa, $behaviouroutput, $options);
            $output .= $this->mark_summary($qa, $behaviouroutput, $options);
            //$output .= $this->question_flag($qa, $options->flags);
        //added verification in case a student is here
        } else {
            //extra verification to prevent error on question preview page
            if (strpos($bodyattributes,'page-question-preview')==false){
                $reflection = new ReflectionClass($qa);
                $property = $reflection->getProperty('usageid');
                $property->setAccessible(true);
                $attemptid = $property->getValue($qa);

                $quizobj =$this->get_quiz_by_id($this->get_quiz_id_by_attemptid($attemptid));

                $t=time();
                $closingtime = $quizobj->timeclose;
                if($closingtime && 
                    $t>$closingtime && 
                    $quizobj->reviewattempt & mod_quiz_display_options::AFTER_CLOSE){
                        $output .= $this->mark_summary($qa, $behaviouroutput, $options);
                }
            }
            else{
                $output .= $this->mark_summary($qa, $behaviouroutput, $options);
            }       
        }
        $output .= $this->question_flag_custom($qa, $options->flags);
        $output .= $this->edit_question_link($qa, $options);
        return $output;
    }
    
    protected function get_quiz_by_id(int $quizid): stdClass {
        global $DB;
        return $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);
    }

    protected function get_quiz_id_by_attemptid(string $attemptid) {
        global $DB;
        return $DB->get_field('quiz_attempts','quiz', array('uniqueid' => $attemptid));
    }

    /**
     * Render the question flag, assuming $flagsoption allows it.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param int $flagsoption the option that says whether flags should be displayed.
     */
    protected function question_flag_custom(question_attempt $qa, $flagsoption) {
        global $CFG;

        $divattributes = array('class' => 'questionflag');

        switch ($flagsoption) {
            /*
            case question_display_options::VISIBLE:
                $flagcontent = $this->get_flag_html_custom($qa->is_flagged());
                break;
            */
            case question_display_options::EDITABLE:
                $id = $qa->get_flag_field_name();
                // The checkbox id must be different from any element name, because
                // of a stupid IE bug:
                // http://www.456bereastreet.com/archive/200802/beware_of_id_and_name_attribute_mixups_when_using_getelementbyid_in_internet_explorer/
                $checkboxattributes = array(
                    'type' => 'checkbox',
                    'id' => $id . 'checkbox',
                    'name' => $id,
                    'value' => 1,
                );
                if ($qa->is_flagged()) {
                    $checkboxattributes['checked'] = 'checked';
                }
                $postdata = question_flags::get_postdata($qa);
                $flagcontent =  html_writer::empty_tag('input',
                                    array('type' => 'hidden', 'name' => $id, 'value' => 0)) .
                                html_writer::empty_tag('input', $checkboxattributes) .
                                html_writer::empty_tag('input',
                                        array('type' => 'hidden', 'value' => $postdata, 'class' => 'questionflagpostdata')) .
                                html_writer::tag('label', $this->get_flag_html_custom($qa->is_flagged(), $id . 'img'),
                                        array('id' => $id . 'label', 'for' => $id . 'checkbox')) . "\n";

                //get_string('unsureattempt', 'theme_quizzer')

                $divattributes = array(
                    'class' => 'questionflag editable',
                    'aria-atomic' => 'true',
                    'aria-relevant' => 'text',
                    'aria-live' => 'assertive',
                );

                break;

            default:
                $flagcontent = '';
        }

        return html_writer::nonempty_tag('div', $flagcontent, $divattributes);
    }

    /**
     * Work out the actual img tag needed for the flag
     *
     * @param bool $flagged whether the question is currently flagged.
     * @param string $id an id to be added as an attribute to the img (optional).
     * @return string the img tag.
     */
    protected function get_flag_html_custom($flagged, $id = '') {
        if ($flagged) {
            $icon = 'i/flagged';
            $alt = get_string('flagged', 'question');
            $label = get_string('unsureattempt', 'theme_quizzer');
        } else {
            $icon = 'i/unflagged';
            $alt = get_string('notflagged', 'question');
            $label = get_string('unsureattempt', 'theme_quizzer');
        }
        $attributes = array(
            'src' => $this->image_url($icon),
            'alt' => $alt,
            'class' => 'questionflagimage',
        );
        if ($id) {
            $attributes['id'] = $id;
        }
        $img = html_writer::empty_tag('img', $attributes);
        $img .= html_writer::span($label);

        return $img;
    }

    /**
     * Generate the display of the question number.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @param question_attempt $qa the question attempt to display.
     * @return HTML fragment.
     */
    protected function number_custom($number, question_attempt $qa) {
        global $DB;

        $questionusageid = $DB->get_field('question_attempts','questionusageid', array('id' => $qa->get_database_id()));
        //$qtotal = $DB->count_records('question_attempts', array('questionusageid' => $questionusageid));

        $sql = "SELECT qa.id, q.id, q.qtype FROM {question_attempts} qa INNER JOIN {question} q ON qa.questionid = q.id WHERE qa.questionusageid = :questionusageid AND q.qtype <> 'description' ORDER BY qa.id ASC";
        $qtotal = count($DB->get_records_sql($sql,array('questionusageid' => $questionusageid)));

        if (trim($number) === '') {
            return '';
        }
        $numbertext = '';

        if (trim($number) === 'i') {
            $numbertext = get_string('information', 'question');
        } else {
            $percentage = round(($number/$qtotal)*100);
            $classperc = ($percentage>50) ? "over50" : '';

            $number = html_writer::tag('span', $number, array('class' => 'qnumber '));
            $qtotal = html_writer::tag('span', " of ".$qtotal, array('class' => 'qtotal small small-60'));

            //$numbertext .= html_writer::start_tag('div', array('class' => "progress-circle p$percentage $classperc")); 
            //$numbertext .= html_writer::start_tag('div', array('class' => "left-half-clipper"));
            //$numbertext .= html_writer::tag('div', '', array('class' => 'first50-bar'));
            //$numbertext .= html_writer::tag('div', '', array('class' => 'value-bar'));
            //$numbertext .= html_writer::end_tag('div');
            $numbertext .= html_writer::tag('span', $number.$qtotal, array('class' => 'qnumber qno'));
            //$numbertext .= html_writer::end_tag('div');
        }
        return html_writer::tag('h3', $numbertext, array('class' => 'no'));
    }

    /**
     *
     * @return HTML fragment.
     */
    protected function highlighters() {
        return html_writer::tag('div','', array('class' => 'text-highlight-pallet'));
    }

    protected function max_mark_question(question_attempt $qa) {
        $output  = '';
        $mark = ($qa->get_max_mark()>1)?"Marks":"Mark";
        $output .= html_writer::start_tag('div', array('class' => 'qmarks'));
        $output .= html_writer::tag('span',$qa->get_max_mark(), array('class' => 'number'));
        $output .= html_writer::tag('span',$mark, array('class' => 'string'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    protected function add_part_marks($highlighters, $marks) {
        return html_writer::tag('div', $highlighters.$marks, array('class' => 'row', 'id' => 'additional-control'));
    }

    /**
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function notes_area($number) {
        global $PAGE;
        $baseurl = strtok($PAGE->url, "?");

        $output  = ''; 
        $output .= html_writer::start_tag('div', array('class' => 'notes'));
        //$output .= html_writer::link(new moodle_url($baseurl, $options),$i+1,array("class" => "qnbutton","data-current-page" => ($page == $i) ? "true" : "false"));
        
        $output .= html_writer::start_tag('div', array('class' => 'questionnotes collapse show', 'aria-expanded'=>"false"));
        $output .= html_writer::tag('div','Question '.$number.' notes', array('class' => 'notetitle'));
        $output .= html_writer::tag('textarea','',array("placeholder"=>"Write here any note you might have about this particular question",'class' => 'notecontent','rows'=>5,'cols'=>'60'));
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $output;
    }
    /*
    <div class="im-controls" id="yui_3_17_2_1_1618433167312_78">
        <a class="notesbutton" data-toggle="collapse" href="#collapseid_2177662" role="button" id="yui_3_17_2_1_1618433167312_77" aria-expanded="true">
            Notes
            <span class="note-toggle" id="yui_3_17_2_1_1618433167312_82"></span>
        </a>
        <div class="questionnotes collapse show" id="collapseid_2177662" aria-expanded="false" style="">
            <div class="notetitle">Question 1 Notes</div>
            <textarea id="q217766:2_-notes_id" name="q217766:2_-notes" rows="5" cols="60"></textarea>
        </div>
    </div>
    */
    
    /**
     * Generate the display of a question in a particular state, and with certain
     * display options. Normally you do not call this method directly. Intsead
     * you call {@link question_usage_by_activity::render_question()} which will
     * call this method with appropriate arguments.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return string HTML representation of the question.
     */
    public function question(question_attempt $qa, qbehaviour_renderer $behaviouroutput,
            qtype_renderer $qtoutput, question_display_options $options, $number) {
        
        $output = '';
        $output .= html_writer::start_tag('div', array(
            'id' => $qa->get_outer_question_div_unique_id(),
            'class' => implode(' ', array(
                'que',
                $qa->get_question(false)->get_type_name(),
                $qa->get_behaviour_name(),
                $qa->get_state_class($options->correctness && $qa->has_marks()),
            ))
        ));

        $output .= html_writer::tag('div',
                $this->info($qa, $behaviouroutput, $qtoutput, $options, $number),
                array('class' => 'info'));

        $output .= html_writer::start_tag('div', array('class' => 'content'));

        //$output .= $this->max_mark_question($qa);
        $output .= $this->add_part_marks( $this->highlighters(), $this->max_mark_question($qa));
        $output .= html_writer::tag('div',
                $this->add_part_heading($qtoutput->formulation_heading(),
                    $this->formulation($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'formulation clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('feedback', 'question'),
                    $this->outcome($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'outcome clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->add_part_heading(get_string('comments', 'question'),
                    $this->manual_comment($qa, $behaviouroutput, $qtoutput, $options)),
                array('class' => 'comment clearfix'));
        $output .= html_writer::nonempty_tag('div',
                $this->response_history($qa, $behaviouroutput, $qtoutput, $options),
                array('class' => 'history clearfix border p-2'));
        //$output .= $this->notes_area($number);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        //Added from Nicolas' renderers.php
        //21-09-2021

        // Only add the files from the upload exam on hybrid question.
        $question_definition = $qa->get_question(false);
        if (get_class($question_definition) == 'qtype_hybrid_question') {

	          $id = optional_param('attempt', 0, PARAM_INT);

	          // Those pages shouldn't have the file list attach to them.
	          $no_file_pages = array('local-qrsub-attempt', 'mod-quiz-attempt');

	          // Make sure we have an attempt id and we are not in a question attempt page.
	          if ($id !== 0 && !in_array($this->page->pagetype, $no_file_pages)) {
	              $attemptobj = quiz_attempt::create($id);

	              // Create a quiz attempt obj, get the uploaded files and add them to the page.
	              $files = qrsub::get_files_from_upload_exam($attemptobj, $qa->get_slot(), $this->output);
	              if (!empty($files)) {
	                  $output = qrsub::add_upload_exam_files_to_review($output, $files);
	              }
	          }
        }
        // QRMOOD-40
        //end Nicolas' addition 
        return $output;
    }
}
class theme_quizzer_core_renderer extends core_renderer {

    /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu_secure($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = get_string('loggedinnot', 'moodle');
            if (!$loginpage) {
                $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);
        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            //<span class="settings-menu-icon" id="yui_3_17_2_1_1619186752848_27"></span>
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }
        $cog = html_writer::empty_tag('img', array('src'=>$this->image_url('pix-settings', 'theme'), 'alt'=>'This is my image'));
        
        $loggeduserinfo = html_writer::span(
            html_writer::span($avatarcontents, $avatarclasses).
            html_writer::span($usertextcontents, 'usertext mr-1'),
            'userbutton'
        );
        $usermenuclasses .= " d-flex";
        $returnstr .= html_writer::span(
            //html_writer::span($avatarcontents, $avatarclasses),
            $cog,
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger(
            $returnstr
        );
        $am->set_action_label(get_string('usermenu'));
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;
                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        if ($value->titleidentifier == "logout,moodle" || $value->titleidentifier == "switchroleto,moodle"|| $value->titleidentifier == "switchrolereturn,moodle"){
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, '', null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        }
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                /*if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }*/
            }
        }
        //$title = html_writer::span($this->render($am).$loggeduserinfo,'header-course-name');
        return html_writer::div(
            $this->render($am).$loggeduserinfo,
            $usermenuclasses
        );
    }

     /**
      * Renders the header bar.
      *
      * @param context_header $contextheader Header bar object.
      * @return string HTML for the header bar.
      */
    protected function render_context_header(context_header $contextheader) {
          global $OUTPUT;

        // Generate the heading first and before everything else as we might have to do an early return.
        if (!isset($contextheader->heading)) {
            $heading = $this->heading($this->page->heading, $contextheader->headinglevel);
        } else {
            $heading = $this->heading($contextheader->heading, $contextheader->headinglevel);
        }

        $showheader = empty($this->page->layout_options['nocontextheader']);
        if (!$showheader) {
            // Return the heading wrapped in an sr-only element so it is only visible to screen-readers.
            return html_writer::div($heading, 'sr-only');
        }
        // All the html stuff goes here.
        $html = html_writer::start_div('page-context-header');

        // Image data.
        if (isset($contextheader->imagedata)) {
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image');
        }
        // Headings.
        $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));
        
        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }
}

/**
 * The renderer for the quiz_grading module.
 *
 * @copyright  2018 Huong Nguyen <huongnv13@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot.'/mod/quiz/report/grading/renderer.php');
class theme_quizzer_quiz_grading_renderer extends quiz_grading_renderer {

    /**
     * Render grade question content.
     *
     * @param question_usage_by_activity $questionusage The question usage that need to grade.
     * @param int $slot the number used to identify this question within this usage.
     * @param question_display_options $displayoptions the display options to use.
     * @param int $questionnumber the number of the question to check.
     * @param string $heading the question heading text.
     * @return string The HTML for the question display.
     */
    public function render_grade_question($questionusage, $slot, $displayoptions, $questionnumber, $heading) {
        $output = '';

        if ($heading) {
            $output .= $this->heading($heading, 4);
        }

        $output .= $questionusage->render_question($slot, $displayoptions, $questionnumber);

        ///////////////////////////////////////////////////////
        // QRMOOD-40 - As a prof, I want the student uploaded files in the same attempt on proctored exam.
        // Adds the student uploaded files in the quiz manual grading report.

        // Get the quiz id from the URL.
        $id = optional_param('id', 0, PARAM_INT);
	$quiz_cmid = get_coursemodule_from_id('quiz',$id);	

        // Get the question attempt from question usage.
        $question_attempt = $questionusage->get_question_attempt($slot);

        // Get the user id from the needsgrading step because, for some reason,
        // the step changes owner once graded.
        $step_iterator = $question_attempt->get_step_iterator();
        foreach($step_iterator as $step) {
            $step_state = $step->get_state();
            if (get_class($step_state) == 'question_state_todo') {
                $user_id = $step->get_user_id();
                break;
            }
        }

        // Create the quiz object and get the file upload attempt.
        $quizobj = quiz::create($quiz_cmid->instance, $user_id);
        $userattempts = quiz_get_user_attempts($quizobj->get_quizid(), $user_id, 'all', true);
        $userattempt = end($userattempts);
        if ($userattempt) {

            // Create a quiz attempt obj, get the uploaded files and add them to the page.
            $attemptobj = quiz_attempt::create($userattempt->id);
            $files = qrsub::get_files_from_upload_exam($attemptobj, $slot, $this->output);
            if (!empty($files)) {
                $output = qrsub::add_upload_exam_files_to_review($output, $files);
            }
        }
        // QRMOOD-40

        return $output;
    }
}
