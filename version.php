<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2016092801;
$plugin->requires = 2016052301; // Moodle 3.1
$plugin->component = 'enrol_studentnumber';
$plugin->release = '3.0 (based on enrol_attributes 2.3.1 for Moodle 2.7-3.1 build 2016080801)';
$plugin->maturity = MATURITY_STABLE;

$plugin->cron = 3600 * 6; // every 6 hours
