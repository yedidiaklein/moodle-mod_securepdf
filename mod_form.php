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
 * The main securepdf configuration form.
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_securepdf
 * @copyright  2020 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/filelib.php');

class mod_securepdf_mod_form extends moodleform_mod {
    /**
     * Defines the securepdf instance configuration form.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $USER, $DB;

        securepdf::check_imagick();

        $mform =& $this->_form;

        // Name and description fields.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name',
                        get_string('maximumchars', '', 255),
                        'maxlength',
                        255,
                        'client');
        moodleform_mod::standard_intro_elements();

        $filemanageroptions = array();
        $filemanageroptions['accepted_types'] = ['.pdf'];
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['mainfile'] = true;

        $mform->addElement('filemanager', 'uploaded', get_string('selectfiles'), null, $filemanageroptions);

        // Standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Prepares the form before data are set.
     *
     * @param array $data to be set
     * @return void
     */
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('uploaded');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_securepdf', 'content', 0, array('subdirs' => true));
            $defaultvalues['uploaded'] = $draftitemid;
        }

    }

    /**
     * Validates the form input
     *
     * @param array $data submitted data
     * @param array $files submitted files
     * @return array eventual errors indexed by the field name
     */
    public function validation($data, $files) {
        $errors = [];
        return $errors;
    }
}
