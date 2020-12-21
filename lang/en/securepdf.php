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
 * English strings for securepdf.
 *
 * @package    mod_securepdf
 * @copyright  2020 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Secure PDF';
$string['modulenameplural'] = 'Secure PDFs';
$string['modulename_help'] = 'Use the securepdf module for adding PDF files securely to your course. The student won\'t be able to download the pdf, It will be shown to him as image for page - without right click to save image';

$string['securepdf:addinstance'] = 'Add a new securepdf';
$string['securepdf:view'] = 'View securepdf';

$string['pluginadministration'] = 'Secure PDF administration';
$string['pluginname'] = 'Secure PDF';

$string['eventpage_view'] = 'Secure PDF page viewed';

$string['resolution'] = 'Default resolution of image';
$string['resolution_explain'] = 'Set the resolution of image from PDF, as higher resolution you are using - the page will load slower';

$string['page'] = 'Page';
$string['nosuchpage'] = 'Error - No such page!';
$string['install_imagick'] = 'PHP-Imagick need to be installed, otherwise you and student won\'t be able to see the content';
$string['imagick_pdf_policy'] = 'You must set the policy of ImageMagick to allow PDF read. See https://stackoverflow.com/questions/52703123/override-default-imagemagick-policy-xml';
$string['cachedef_pages'] = 'Pages from PDF cache';
$string['imagickrequired'] = 'PHP Imagemagick extension is required';