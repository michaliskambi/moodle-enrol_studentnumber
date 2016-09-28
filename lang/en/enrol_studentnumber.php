<?php
/**
 * @package    enrol_studentnumber
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>, Michalis Kamburelis <michalis.kambi@gmail.com>
 * @copyright  2012-2015 Université de Lausanne (@link http://www.unil.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Enrol by studentnumber';
$string['defaultrole'] = 'Default role';
$string['defaultrole_desc'] = 'Default role used to enrol people with this plugin (each instance can override this).';
$string['attrsyntax'] = 'User profile fields rules';
$string['attrsyntax_help'] = '<p>These rules can only use custom user profile fields.</p>';
$string['studentnumber:config'] = 'Configure plugin instances';
$string['studentnumber:manage'] = 'Manage enrolled users';
$string['studentnumber:unenrol'] = 'Unenrol users from the course';
$string['studentnumber:unenrolself'] = 'Unenrol self from the course';
$string['mappings'] = 'Shibboleth mappings';
$string['mappings_desc'] =
        'When using Shibboleth authentication, this plugin can automatically update a user\'s profile upon each login.<br><br>For instance, if you want to update the user\'s <code>homeorganizationtype</code> profile field with the Shibboleth attribute <code>Shib-HomeOrganizationType</code> (provided that is the environment variable available to the server during login), you can enter on one line: <code>Shib-HomeOrganizationType:homeorganizationtype</code><br>You may enter as many lines as needed.<br><br>To not use this feature or if you don\'t use Shibboleth authentication, simple leave this empty.';
$string['profilefields'] = 'Profile fields to be used in the selector';
$string['profilefields_desc'] =
        'Which user profile fields can be used when configuring an enrolment instance?<br><br><b>If you don\'t select any role here, this makes the plugin moot and hence disables its use in courses.</b><br>The feature below may however still be used in this case.';
$string['removewhenexpired'] = 'Unenrol after attributes expiration';
$string['removewhenexpired_help'] = 'Unenrol users upon login if they don\'t match the attribute rule anymore.';

