<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>,  Michalis Kamburelis
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * See https://docs.moodle.org/dev/Enrolment_plugins
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Database enrolment plugin implementation.
 *
 * @author  Michalis Kamburelis, Petr Skoda - based on code by Martin Dougiamas, Martin Langhoff and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_studentnumber_plugin extends enrol_plugin {
    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     *
     * @return bool
     */
    public function instance_deleteable($instance) {
        return true;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     *
     * @param int $courseid
     *
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/studentnumber:config',
                        $context)
        ) {
            return null;
        }

        // multiple instances supported - different roles with different password
        return new moodle_url('/enrol/studentnumber/edit.php', array('courseid' => $courseid));
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     *
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);

        return has_capability('enrol/studentnumber:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     *
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);

        return has_capability('enrol/studentnumber:config', $context);
    }

    /**
     * Returns edit icons for the page with list of instances
     *
     * @param stdClass $instance
     *
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'studentnumber') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/studentnumber:config', $context)) {
            $editlink = new moodle_url("/enrol/studentnumber/edit.php", array(
                    'courseid' => $instance->courseid,
                    'id'       => $instance->id
            ));
            $icons[] = $OUTPUT->action_icon($editlink,
                    new pix_icon('i/edit', get_string('edit'), 'core', array('class' => 'icon')));
        }

        return $icons;
    }

    public static function studentnumbers_tolist($text)
    {
        $result = array();

        // Based on https://evertpot.com/222/
        $stream = fopen('php://memory','r+');
        fwrite($stream, $text);
        rewind($stream);

        if (strstr($text, ',') !== FALSE) {
            /* read CSV */
            while (($data = fgetcsv($stream, 0, ",")) !== FALSE)  {
                if ($data === NULL) { // empty line
                    continue;
                }
                if (count($data) != 4) {
                    print_error('Błędny plik CSV, linia nie ma dokładnie 4 kolumn: ' . print_r($data, true));
                }
                $result[] = trim($data[2]);
            }
        } else {
            /* read one studentnumber per line */
            while ($line = fgets($stream)) {
                $line = trim($line);
                if ($line != '') {
                    $result[] = $line;
                }
            }
        }
        fclose($stream);

        return $result;
    }

    public static function studentnumbers_tosql($text) {
        global $CFG, $DB;
        $select = '';
        $where = '(1=0)';
        $params = array();
        foreach (self::studentnumbers_tolist($text) as $studentnumber) {
            $where .= ' or (u.username =  ?)';
            $params[] = $studentnumber;
        }
        return array(
                'select' => $select,
                'where'  => $where,
                'params' => $params
        );
    }

    public function cron() {
        $this->process_enrolments();
    }

    public static function process_login(\core\event\user_loggedin $event) {
        global $CFG, $DB;
        // we just received the event from the authentication system; check if well-formed:
        if (!$event->userid) {
            // didn't get an user ID, return as there is nothing we can do
            return true;
        }
        // process the actual enrolments
        self::process_enrolments($event);
    }

    public static function process_enrolments($event = null, $instanceid = null) {
        global $DB;
        $nbenrolled = 0;
        $possible_unenrolments = array();

        if ($instanceid) {
            // We're processing one particular instance, making sure it's active
            $enrol_studentnumber_records = $DB->get_records('enrol', array(
                    'enrol'  => 'studentnumber',
                    'status' => 0,
                    'id'     => $instanceid
            ));
        }
        else {
            // We're processing all active instances,
            // because a user just logged in
            // OR we're running the cron
            $enrol_studentnumber_records = $DB->get_records('enrol', array(
                    'enrol'  => 'studentnumber',
                    'status' => 0
            ));
            if (!is_null($event)) {
                // Let's check if there are any potential unenroling instances
                $userid = (int)$event->userid;
                $possible_unenrolments =
                        $DB->get_records_sql("SELECT id, enrolid FROM {user_enrolments} WHERE userid = ? AND status = 0 AND enrolid IN ( SELECT id FROM {enrol} WHERE enrol = 'studentnumber' AND customint1 = 1 ) ",
                                array($userid));
            }
        }

        // are we to unenrol from anywhere?
        foreach ($possible_unenrolments as $id => $user_enrolment) {

            $unenrol_studentnumber_record = $DB->get_record('enrol', array(
                    'enrol'      => 'studentnumber',
                    'status'     => 0,
                    'customint1' => 1,
                    'id'         => $user_enrolment->enrolid
            ));
            if (!$unenrol_studentnumber_record) {
                continue;
            }

            $select = 'SELECT DISTINCT u.id FROM {user} u';
            $where = ' WHERE u.id=' . $userid . ' AND u.deleted=0 AND ';
            $arraysql = self::studentnumbers_tosql($unenrol_studentnumber_record->customtext1);
            $users = $DB->get_records_sql($select . $arraysql['select'] . $where . $arraysql['where'],
                    $arraysql['params']);

            if (!array_key_exists($userid, $users)) {
                $enrol_studentnumber_instance = new enrol_studentnumber_plugin();
                $enrol_studentnumber_instance->unenrol_user($unenrol_studentnumber_record, (int)$userid);
            }
        }

        // are we to enrol anywhere?
        foreach ($enrol_studentnumber_records as $enrol_studentnumber_record) {

            $enrol_studentnumber_instance = new enrol_studentnumber_plugin();
            $enrol_studentnumber_instance->name = $enrol_studentnumber_record->name;

            $select = 'SELECT DISTINCT u.id FROM {user} u';
            if ($event) { // called by an event, i.e. user login
                $userid = (int)$event->userid;
                $where = ' WHERE u.id=' . $userid;
            }
            else { // called by cron or by construct
                $where = ' WHERE 1=1';
            }
            $where .= ' AND u.deleted=0 AND ';
            $arraysql = self::studentnumbers_tosql($enrol_studentnumber_record->customtext1);

            $users = $DB->get_records_sql($select . $arraysql['select'] . $where . $arraysql['where'],
                    $arraysql['params']);
            foreach ($users as $user) {
                if (is_enrolled(context_course::instance($enrol_studentnumber_record->courseid), $user)) {
                    continue;
                }
                $enrol_studentnumber_instance->enrol_user($enrol_studentnumber_record, $user->id,
                        $enrol_studentnumber_record->roleid);
                $nbenrolled++;
            }
        }

        if (!$event && !$instanceid) {
            // we only want output if runnning within the cron
            mtrace('enrol_studentnumber : enrolled ' . $nbenrolled . ' users.');
        }

        return $nbenrolled;
    }

    /*
     *
     */
    public static function purge_instance($instanceid, $context) {
        if (!$instanceid) {
            return false;
        }
        global $DB;
        if (!$DB->delete_records('role_assignments', array(
                'component' => 'enrol_studentnumber',
                'itemid'    => $instanceid
        ))
        ) {
            return false;
        }
        if (!$DB->delete_records('user_enrolments', array('enrolid' => $instanceid))) {
            return false;
        }
        $context->mark_dirty();

        return true;
    }

    /**
     * Returns enrolment instance manage link.
     *
     * By defaults looks for manage.php file and tests for manage capability.
     *
     * @param navigation_node $instancesnode
     * @param stdClass        $instance
     *
     * @return moodle_url;
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'studentnumber') {
            throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/studentnumber:config', $context)) {
            $managelink = new moodle_url('/enrol/studentnumber/edit.php', array(
                    'courseid' => $instance->courseid,
                    'id'       => $instance->id
            ));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

}
