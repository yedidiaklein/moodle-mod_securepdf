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
 * Create cache task for securepdf
 *
 * @package    mod_securepdf
 * @copyright  2021 Yedidia Klein <yedidia@openapp.co.il>
 * @since      Moodle 3.1
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_securepdf\task;

/**
 * Adhoc task that renders and caches all pages of a securepdf activity.
 *
 * @package    mod_securepdf
 * @copyright  2021 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_cache extends \core\task\adhoc_task {
    /**
     * Run the task.
     */
    public function execute() {
        $data = $this->get_custom_data();
        $moduleid = $data->moduleid;
        // Init cache object.
        $cache = \cache::make('mod_securepdf', 'pages');
        // Read Module file.
        $context = \context_module::instance($moduleid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_securepdf', 'content', 0, 'sortorder', false);
        $content = null;
        foreach ($files as $file) {
            // Never hand a non-PDF file (e.g. PostScript renamed to .pdf) to ImageMagick.
            if (!\mod_securepdf\pdf_validator::is_pdf_file($file)) {
                mtrace('[mod_securepdf] Skipping module ' . $moduleid . ': file is not a valid PDF.');
                return;
            }
            $content = $file->get_content();
        }
        if ($content === null) {
            mtrace('[mod_securepdf] No file found for module ' . $moduleid . '.');
            return;
        }

        // Init imagick object.
        $im = new \Imagick();

        $settings = get_config('securepdf');
        $im->setResolution($settings->resolution, $settings->resolution);
        try {
            \mod_securepdf\pdf_validator::read_into_imagick($im, $content);
        } catch (\Exception $e) {
            mtrace('[mod_securepdf] ' . $e->getMessage());
            $im->destroy();
            return;
        }
        $numpages = $im->getNumberImages();
        $result = $cache->set($moduleid, $numpages);

        for ($page = 0; $page < $numpages; $page++) {
            mtrace('[mod_securepdf] Caching page ' . $page . ' of module ' . $moduleid);
            $im->setIteratorIndex($page);
            $im->setImageFormat('jpeg');
            $im->setImageAlphaChannel(\Imagick::VIRTUALPIXELMETHOD_WHITE);
            $img = $im->getImageBlob();
            $base64 = base64_encode($img);
            $result = $cache->set($moduleid . '_' . $page, $base64);
        }
        $im->destroy();
    }
}
