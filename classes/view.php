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
 * Class for securepdf view.
 *
 * @package    mod_securepdf
 * @copyright  2021 Yedidia Klein <yedidia@openapp.co.il>
 * @since      Moodle 3.1
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_securepdf;

defined('MOODLE_INTERNAL') || die();

/**
 * Class view
 *
 * @package mod_securepdf
 */
class view {
    /**
     * Check if there is an exisiting cache on numpages and data.
     *
     * @return array
     */
    public static function checkcache($cm, $page) {
         // Use cache if image is cached, instead of parsing the PDF again.
        $cache = \cache::make('mod_securepdf', 'pages');
        $data = $cache->get($cm->id . '_' . $page);
        $numpages = $cache->get($cm->id);
        return ['data' => $data, 'numpages' => $numpages];
    }
    /**
     * Get the number of pages in the PDF and return the image data.
     * in case that there is no cache.
     *
     * @param \context $context
     * @param int $resolution
     * @param \cm_info $cm
     * @param int $page
     * @return array
     */
    public static function getnumpages($context, $resolution, $cm, $page = 0) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_securepdf', 'content', 0, 'sortorder', false);
        foreach ($files as $file) {
            $content = $file->get_content();
        }

        $im = new \imagick();
        $im->setResolution($resolution, $resolution);
        try {
            $im->readImageBlob($content);
        } catch (Exception $e) {
            echo $OUTPUT->header();
            \core\notification::error(get_string('imagick_pdf_policy', 'mod_securepdf'));
            echo $e;
            echo $OUTPUT->footer();
            die();
        }
        $numpages = $im->getNumberImages();
        // Cache the number of pages.
        $cache = \cache::make('mod_securepdf', 'pages');
        $result = $cache->set($cm->id, $numpages);

        $base64 = '';

        if ($page > 0) {
            $im->setIteratorIndex($page);
            $im->setImageFormat('jpeg');
            $im->setImageAlphaChannel(\Imagick::VIRTUALPIXELMETHOD_WHITE);
            $img = $im->getImageBlob();
            $base64 = base64_encode($img);
            // Cache the image.
            $result = $cache->set($cm->id . '_' . $page, $base64);
        } 
        $im->destroy();
        return ['numpages' => $numpages, 'data' => $base64];
    }
    /**
     * Add watermark to the image.
     *
     * @param string $base64
     * @param object $settings
     * @return string
     */
    public static function addwatermark($base64, $settings) {
        global $USER, $SITE, $CFG;
        // Add username and site name to the image.
        $text = '';
        if ($settings->addusername) {
            $text .= $USER->firstname . ' ' . $USER->lastname . ' (' . $USER->username . ')';
        }
        if ($settings->addsiteaddress) {
            if ($text != '') {
                $text .= ' - ';
            }
            $text .= $SITE->fullname;
        }

        if ($text != '') {
            $gd = \imagecreatefromstring(base64_decode($base64));
            if ($gd) {
                $color = \imagecolorallocate($gd, 0, 0, 0);
                $white = \imagecolorallocate($gd, 255, 255, 255);
                $font = $CFG->dirroot . '/mod/securepdf/font/NotoSans.ttf';
                $size = 12;
                $angle = 0;
                $bbox = \imagettfbbox($size, $angle, $font, $text);
                $x = $bbox[0] + \round(imagesx($gd) / 2) - \round($bbox[4] / 2);
                if ($settings->usernameposition == 'top') {
                    $y = 15;
                } else if ($settings->usernameposition == 'middle') {
                    $y = \round(imagesy($gd) / 2) - round($bbox[5] / 2);
                } else {
                    $y = \imagesy($gd) - 20;
                }
                \imagettftext($gd, $size, $angle, $x, $y, $color, $font, $text);
                // Add white shadow to the text (for dark documents).
                \imagettftext($gd, $size, $angle, $x + 1, $y + 1, $white, $font, $text);
                \ob_start();
                \imagejpeg($gd);
                $base64 = \base64_encode(ob_get_clean());
                \imagedestroy($gd);
            } else {
                \core\notification::error(get_string('nogdobject', 'mod_securepdf'));
            }
        }
        return $base64;
    }

}