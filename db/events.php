<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>, Michalis Kamburelis <michalis.kambi@gmail.com>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$observers = array(

        array(
                'eventname'   => '\core\event\user_loggedin',
                'callback'    => 'enrol_studentnumber_plugin::process_login',
                'includefile' => '/enrol/studentnumber/lib.php',
                'internal'    => true,
                'priority'    => 9999,
        ),

);

