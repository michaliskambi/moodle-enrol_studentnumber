<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // 1. Default role

    $options = get_default_enrol_roles(context_system::instance());

    $student = get_archetype_roles('student');
    $student_role = array_shift($student);

    //    $settings->add(new admin_setting_heading('enrol_myunil_defaults', get_string('enrolinstancedefaults', 'admin'),
    //            ''));
    $settings->add(new admin_setting_configselect('enrol_studentnumber/default_roleid',
            get_string('defaultrole', 'enrol_studentnumber'), get_string('defaultrole_desc', 'enrol_studentnumber'),
            $student_role->id, $options));

    // 2. Fields to use in the selector
    $customfieldrecords = $DB->get_records('user_info_field');
    if ($customfieldrecords) {
        $customfields = [];
        foreach ($customfieldrecords as $customfieldrecord) {
            $customfields[$customfieldrecord->shortname] = $customfieldrecord->name;
        }
        asort($customfields);
        $settings->add(new admin_setting_configmultiselect('enrol_studentnumber/profilefields',
                get_string('profilefields', 'enrol_studentnumber'), get_string('profilefields_desc', 'enrol_studentnumber'),
                [], $customfields));
    }

    // 3. Fields to update via Shibboleth login
    if (in_array('shibboleth', get_enabled_auth_plugins())) {
        $settings->add(new admin_setting_configtextarea('enrol_studentnumber/mappings',
                get_string('mappings', 'enrol_studentnumber'), get_string('mappings_desc', 'enrol_studentnumber'), '',
                PARAM_TEXT, 60, 10));
    }
}

