<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * securepdf module admin settings and defaults.
 *
 * @package    mod_securepdf
 * @copyright  2020 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->libdir . '/resourcelib.php');

    $displayoptions = resourcelib_get_displayoptions(
        array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

    // Resolution.
    $settings->add(
        new admin_setting_configtext('securepdf/resolution',
                                     get_string('resolution', 'securepdf'),
                                     get_string('resolution_explain', 'securepdf'),
                                     "150",
                                     PARAM_INT
                                     ));
    // Add username to image.
    $settings->add(
        new admin_setting_configcheckbox('securepdf/addusername',
                                         get_string('addusername', 'securepdf'),
                                         get_string('addusername_explain', 'securepdf'),
                                         0
                                         ));
    // Add site name to image.
    $settings->add(
        new admin_setting_configcheckbox('securepdf/addsiteaddress',
                                         get_string('addsiteaddress', 'securepdf'),
                                         get_string('addsiteaddress_explain', 'securepdf'),
                                         0
                                         ));
    // Location of username and site info.
    $settings->add(
        new admin_setting_configselect('securepdf/usernameposition',
                                       get_string('usernameposition', 'securepdf'),
                                       get_string('usernameposition_explain', 'securepdf'),
                                       "bottom",
                                       [ "top" => get_string('top', 'securepdf'),
                                             "bottom" => get_string('bottom', 'securepdf'),
                                             "middle" => get_string('middle', 'securepdf') ]
                                       ));
}
