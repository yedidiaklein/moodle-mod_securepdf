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

defined('MOODLE_INTERNAL') || die();

class create_cache extends \core\task\adhoc_task
{
    public function execute()
    {
        $data = $this->get_custom_data();
        $moduleid = $data->moduleid;
        // Init cache object
        $cache = \cache::make('mod_securepdf', 'pages');
        // Read Module file
        $context = \context_module::instance($moduleid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_securepdf', 'content', 0, 'sortorder', false);
        foreach ($files as $file) {
            $content = $file->get_content();
        }

        // Init imagick object
        $im = new \imagick();

        $settings = get_config('securepdf');
        $im->setResolution($settings->resolution, $settings->resolution);
        try {
            $im->readImageBlob($content);
        } catch (Exception $e) {
            echo '[mod_securepdf]' . $e . "\n";
        }
        $numpages = $im->getNumberImages();
        $result = $cache->set($moduleid, $numpages);

        for ($page = 0; $page < $numpages; $page++) {
            echo '[mod_securepdf] Caching page ' . $page . ' of module ' . $moduleid . "\n";
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
