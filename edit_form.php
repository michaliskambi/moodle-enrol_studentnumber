<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class enrol_studentnumber_edit_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_studentnumber'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        }
        else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('default_roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('role'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('default_roleid'));

        $mform->addElement('textarea', 'customtext1', get_string('studentnumbers', 'enrol_studentnumber'), array(
                'cols' => '60',
                'rows' => '8'
        ));

        $mform->addElement('checkbox', 'customint1', get_string('removewhenexpired', 'enrol_studentnumber'));
        $mform->addHelpButton('customint1', 'removewhenexpired', 'enrol_studentnumber');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();

        $this->set_data($instance);
    }
}
