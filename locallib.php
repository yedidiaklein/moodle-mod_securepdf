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
 * This class provides functionality for the securepdf module.
 *
 * @package   mod_securepdf
 * @copyright 2020 Yedidia Klein <yedidia@openapp.co.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Standard base class for mod_securepdf.
 */
class securepdf {
    /** @var stdClass The securepdf record that contains the
     *                global settings for this securepdf instance.
     */
    private $instance;

    /** @var context The context of the course module for this securepdf instance
     *               (or just the course if we are creating a new one).
     */
    private $context;

    /** @var stdClass The course this securepdf instance belongs to */
    private $course;

    /** @var securepdf_renderer The custom renderer for this module */
    private $output;

    /** @var stdClass The course module for this securepdf instance */
    private $coursemodule;

    /** @var string modulename Prevents excessive calls to get_string */
    private static $modulename = null;

    /** @var string modulenameplural Prevents excessive calls to get_string */
    private static $modulenameplural = null;

    /**
     * Constructor for the base securepdf class.
     *
     * @param mixed $coursemodulecontext context|null The course module context
     *                                   (or the course context if the coursemodule
     *                                   has not been created yet).
     * @param mixed $coursemodule The current course module if it was already loaded,
     *                            otherwise this class will load one from the context
     *                            as required.
     * @param mixed $course The current course if it was already loaded,
     *                      otherwise this class will load one from the context as
     *                      required.
     */
    public function __construct($coursemodulecontext, $coursemodule, $course) {
        global $PAGE;

        $this->context = $coursemodulecontext;
        $this->coursemodule = $coursemodule;
        $this->course = $course;
    }

    /**
     * Set the course data.
     *
     * @param stdClass $course The course data
     */
    public function set_course(stdClass $course) {
        $this->course = $course;
    }

    /**
     * Add this instance to the database.
     *
     * @param stdClass $formdata The data submitted from the form
     * @return mixed False if an error occurs or the int id of the new instance
     */
    public function add_instance(stdClass $formdata) {
        global $DB;

        $fs = get_file_storage();
        $cmid = $formdata->coursemodule;
        $draftitemid = $formdata->uploaded;

        $context = context_module::instance($cmid);
        if ($draftitemid) {
            $options = array('subdirs' => false);
            file_save_draft_area_files($draftitemid, $context->id, 'mod_securepdf', 'content', 0, $options);
        }
        $file = $fs->get_area_files($context->id, 'mod_securepdf', 'content', 0, 'sortorder', false);

        // Add the database record.
        $add = new stdClass();
        $add->name = $formdata->name;
        $add->timemodified = time();
        $add->timecreated = time();
        $add->course = $formdata->course;
        $add->courseid = $formdata->course;
        $add->intro = $formdata->intro;
        $add->introformat = $formdata->introformat;
        $add->fileid = 1;

        $returnid = $DB->insert_record('securepdf', $add);
        $this->instance = $DB->get_record('securepdf',
                                          array('id' => $returnid),
                                          '*',
                                          MUST_EXIST);

        // Cache the course record.
        $this->course = $DB->get_record('course',
                                        array('id' => $formdata->course),
                                        '*',
                                        MUST_EXIST);

        return $returnid;
    }

    /**
     * Delete this instance from the database.
     *
     * @return bool False if an error occurs
     */
    public function delete_instance() {
        global $DB;
        $result = true;

        // Delete files associated with this securepdf.
        $fs = get_file_storage();
        if (! $fs->delete_area_files($this->context->id) ) {
            $result = false;
        }

        $cmid = $this->get_instance()->id;
        // Delete the instance.
        // Note: all context files are deleted automatically.
        $DB->delete_records('securepdf', array('id' => $cmid));

        // Delete pages cache.
        $cache = cache::make('mod_securepdf', 'pages');
        $numpages = $cache->get($cmid);
        if ($numpages) {
            for ($i = 0; $i++; $i < $numpages) {
                $cache->delete($cmid . '_' . $i);
            }
            $cache->delete($cmid);
        }
        return $result;
    }

    /**
     * Update this instance in the database.
     *
     * @param stdClass $formdata The data submitted from the form
     * @return bool False if an error occurs
     */
    public function update_instance($formdata) {
        global $DB;

        $fs = get_file_storage();
        $cmid = $formdata->coursemodule;
        $draftitemid = $formdata->uploaded;

        $context = context_module::instance($cmid);
        if ($draftitemid) {
            $options = array('subdirs' => false);
            file_save_draft_area_files($draftitemid, $context->id, 'mod_securepdf', 'content', 0, $options);
        }
        $file = $fs->get_area_files($context->id, 'mod_securepdf', 'content', 0, 'sortorder', false);

        $update = new stdClass();
        $update->id = $formdata->instance;
        $update->name = $formdata->name;
        $update->timemodified = time();
        $update->course = $formdata->course;
        $update->intro = $formdata->intro;
        $update->introformat = $formdata->introformat;
        $update->file = 1;

        $result = $DB->update_record('securepdf', $update);
        $this->instance = $DB->get_record('securepdf',
                                          array('id' => $update->id),
                                          '*',
                                          MUST_EXIST);

        // Delete pages cache for cases that file was changed.
        $cache = cache::make('mod_securepdf', 'pages');
        $numpages = $cache->get($cmid);
        if ($numpages) {
            for ($i = 0; $i++; $i < $numpages) {
                $cache->delete($cmid . '_' . $i);
            }
            $cache->delete($cmid);
        }

        return $result;
    }

    /**
     * Get the name of the current module.
     *
     * @return string The module name (securepdf)
     */
    protected function get_module_name() {
        if (isset(self::$modulename)) {
            return self::$modulename;
        }
        self::$modulename = get_string('modulename', 'securepdf');
        return self::$modulename;
    }

    /**
     * Get the plural name of the current module.
     *
     * @return string The module name plural (securepdfs)
     */
    protected function get_module_name_plural() {
        if (isset(self::$modulenameplural)) {
            return self::$modulenameplural;
        }
        self::$modulenameplural = get_string('modulenameplural', 'securepdf');
        return self::$modulenameplural;
    }

    /**
     * Has this securepdf been constructed from an instance?
     *
     * @return bool
     */
    public function has_instance() {
        return $this->instance || $this->get_course_module();
    }

    /**
     * Get the settings for the current instance of this securepdf.
     *
     * @return stdClass The settings
     */
    public function get_instance() {
        global $DB;
        if ($this->instance) {
            return $this->instance;
        }
        if ($this->get_course_module()) {
            $params = array('id' => $this->get_course_module()->instance);
            $this->instance = $DB->get_record('securepdf', $params, '*', MUST_EXIST);
        }
        if (!$this->instance) {
            throw new coding_exception('Improper use of the securepdf class. ' .
                                       'Cannot load the securepdf record.');
        }
        return $this->instance;
    }

    /**
     * Get the context of the current course.
     *
     * @return mixed context|null The course context
     */
    public function get_course_context() {
        if (!$this->context && !$this->course) {
            throw new coding_exception('Improper use of the securepdf class. ' .
                                       'Cannot load the course context.');
        }
        if ($this->context) {
            return $this->context->get_course_context();
        } else {
            return context_course::instance($this->course->id);
        }
    }

    /**
     * Get the current course module.
     *
     * @return mixed stdClass|null The course module
     */
    public function get_course_module() {
        if ($this->coursemodule) {
            return $this->coursemodule;
        }
        if (!$this->context) {
            return null;
        }

        if ($this->context->contextlevel == CONTEXT_MODULE) {
            $this->coursemodule = get_coursemodule_from_id('securepdf',
                                                           $this->context->instanceid,
                                                           0,
                                                           false,
                                                           MUST_EXIST);
            return $this->coursemodule;
        }
        return null;
    }

    /**
     * Get context module.
     *
     * @return context
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Get the current course.
     *
     * @return mixed stdClass|null The course
     */
    public function get_course() {
        global $DB;

        if ($this->course) {
            return $this->course;
        }

        if (!$this->context) {
            return null;
        }
        $params = array('id' => $this->get_course_context()->instanceid);
        $this->course = $DB->get_record('course', $params, '*', MUST_EXIST);

        return $this->course;
    }

    /**
     * Lazy load the page renderer and expose the renderer to plugins.
     *
     * @return securepdf_renderer
     */
    public function get_renderer() {
        global $PAGE;

        if ($this->output) {
            return $this->output;
        }
        $this->output = $PAGE->get_renderer('mod_securepdf');
        return $this->output;
    }
    /**
     * Util function to check if needed php-imagick is installed.
     *
     * @return true if installed, false if not.
     */

    public static function check_imagick() {
        $ext = get_loaded_extensions();
        if (!array_search('imagick', $ext)) {
            \core\notification::error(get_string('install_imagick', 'mod_securepdf'));
            return false;
        } else {
            return true;
        }
    }
}
