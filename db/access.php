<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

        'enrol/studentnumber:config' => array(
                'captype'      => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'manager' => CAP_ALLOW,
                )
        ),

);

