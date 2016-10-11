<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>, Michalis Kamburelis <michalis.kambi@gmail.com>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('edit_form.php');

$courseid = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT); // instanceid

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('enrol/studentnumber:config', $context);

$PAGE->set_url('/enrol/studentnumber/edit.php', array(
        'courseid' => $course->id,
        'id'       => $instanceid
));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
if (!enrol_is_enabled('studentnumber')) {
    redirect($return);
}

$plugin = enrol_get_plugin('studentnumber');

if ($instanceid) {
    $instance = $DB->get_record('enrol', array(
            'courseid' => $course->id,
            'enrol'    => 'studentnumber',
            'id'       => $instanceid
    ), '*', MUST_EXIST);
}
else {
    require_capability('moodle/course:enrolconfig', $context);
    // no instance yet, we have to add new instance
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = new stdClass();
    $instance->id = null;
    $instance->courseid = $course->id;
}

$mform = new enrol_studentnumber_edit_form(null, array(
        $instance,
        $plugin,
        $context
));

$nbenrolled = 0;
$nbunenrolled = 0;

if ($mform->is_cancelled()) {
    redirect($return);
}
else if ($data = $mform->get_data()) {

    if ($instance->id) {
        $instance->name = $data->name;
        $instance->roleid = $data->roleid;
        $instance->customint1 = isset($data->customint1) ? ($data->customint1) : 0;
        $instance->customtext1 = $data->customtext1;
        $DB->update_record('enrol', $instance);
    }
    else {
        $fields = array(
                'name'        => $data->name,
                'roleid'      => $data->roleid,
                'customint1'  => isset($data->customint1),
                'customtext1' => $data->customtext1
        );
        $plugin->add_instance($course, $fields);
    }

    // redirect($return);
    // do not redirect, stay on this page to show process_all_enrolments_and_unenrolments results

    if ($instance->id) {
        enrol_studentnumber_plugin::process_all_enrolments_and_unenrolments(
            $instance->id, $nbenrolled, $nbunenrolled);
    }
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_studentnumber'));

//$PAGE->requires->jquery();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_studentnumber'));

if ($nbenrolled != 0 || $nbunenrolled != 0) {
    echo '<p class="alert alert-info">Stan zapisów uległ zmianie. Zapisano: ' . $nbenrolled . ', wypisano: ' . $nbunenrolled . '</p>';
}

$mform->display();

// DEBUGGING
/*
if ($instanceid) {
    echo '<h2>Debug</h2>';
    echo '<hr>customtext1<br>';
    print_r($instance->customtext1);
    echo '<hr>studentnumbers_tolist count<br>';
    print_r(count(enrol_studentnumber_plugin::studentnumbers_tolist($instance->customtext1)));
    echo '<hr>studentnumbers_tolist<br>';
    print_r(enrol_studentnumber_plugin::studentnumbers_tolist($instance->customtext1));
    echo '<hr>studentnumbers_tosql<br>';
    $debug_arraysql = enrol_studentnumber_plugin::studentnumbers_tosql($instance->customtext1);
    print_r($debug_arraysql);
    echo '<hr>sql<br>';
    $debug_sqlquery =  'SELECT DISTINCT u.id FROM {user} u ' . $debug_arraysql['select'] . ' WHERE ' . $debug_arraysql['where'];
    echo $debug_sqlquery;
    echo '<hr>users count<br>';
    $debug_users = $DB->get_records_sql($debug_sqlquery, $debug_arraysql['params']);
    echo count($debug_users);
}
*/

if (isset($instance->customtext1)) {
    $debug_count_all = count(enrol_studentnumber_plugin::studentnumbers_tolist($instance->customtext1));
    $debug_arraysql = enrol_studentnumber_plugin::studentnumbers_tosql($instance->customtext1);
    $debug_sqlquery = 'SELECT DISTINCT u.id FROM {user} u ' . $debug_arraysql['select'] . ' WHERE ' . $debug_arraysql['where'];
    $debug_count_existing = count($DB->get_records_sql($debug_sqlquery, $debug_arraysql['params']));
    echo '<p>Dane z ostatniego zapisu ustawień: ' . $debug_count_all . ' numerów indeksów, z tego ' . $debug_count_existing . ' już ma konto w Moodle.</p>';
}

echo $OUTPUT->footer();
