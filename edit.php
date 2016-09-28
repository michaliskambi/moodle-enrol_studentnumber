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

    redirect($return);
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_studentnumber'));

//$PAGE->requires->jquery();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_studentnumber'));
$mform->display();

// DEBUGGING : BEGIN
if ($instanceid) {
    debugging('customtext1= ' . print_r(json_decode($instance->customtext1), true), DEBUG_DEVELOPER);
    $debug_fieldsandrules = enrol_studentnumber_plugin::attrsyntax_toarray($instance->customtext1);
    debugging('fieldsandrules= ' . print_r($debug_fieldsandrules, true), DEBUG_DEVELOPER);
    $debug_arraysql = enrol_studentnumber_plugin::arraysyntax_tosql($debug_fieldsandrules);
    debugging('arraysql= ' . print_r($debug_arraysql, true), DEBUG_DEVELOPER);
    $debug_sqlquery =
            'SELECT DISTINCT u.id FROM {user} u ' . $debug_arraysql['select'] . ' WHERE ' . $debug_arraysql['where'];
    debugging('sqlquery= ' . print_r($debug_sqlquery, true), DEBUG_DEVELOPER);
    $debug_users = $DB->get_records_sql($debug_sqlquery, $debug_arraysql['params']);
    debugging('countusers= ' . print_r(count($debug_users), true), DEBUG_DEVELOPER);
    //    debugging('force.php DEBUGGING:', DEBUG_DEVELOPER);
    //    $nbenrolled = enrol_studentnumber_plugin::process_enrolments(null, $instanceid);
    //    debugging('nbenrolled= ' . print_r($nbenrolled, true), DEBUG_DEVELOPER);
}
// DEBUGGING : END

echo $OUTPUT->footer();
