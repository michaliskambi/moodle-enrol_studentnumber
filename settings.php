<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>, Michalis Kamburelis <michalis.kambi@gmail.com>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch), 2016 skos.ii.uni.wroc.pl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $options = get_default_enrol_roles(context_system::instance());

    $student = get_archetype_roles('student');
    $student_role = array_shift($student);

    $settings->add(new admin_setting_configselect('enrol_studentnumber/default_roleid',
            get_string('defaultrole', 'enrol_studentnumber'), get_string('defaultrole_desc', 'enrol_studentnumber'),
            $student_role->id, $options));
}
