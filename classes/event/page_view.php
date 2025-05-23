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
 * The page_view event.
 *
 * @package    securepdf
 * @copyright  2017 Yedidia@openapp.co.il
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_securepdf\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The page_view event class.
 *
 **/
class page_view extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'securepdf';
    }

    public static function get_name() {
            return get_string('eventpage_view', 'securepdf');
    }

    public function get_description() {
        if ($this->other != "") {
            return "The user with id {$this->userid} Viewed page : <b>{$this->other}</b> on module id {$this->objectid}.";
        } else {
            return "The user with id {$this->userid} viewed module id {$this->objectid}.";
        }
    }

    public function get_url() {
        return new \moodle_url('/mod/securepdf/view.php', array('id' => $this->contextinstanceid));
    }
}
