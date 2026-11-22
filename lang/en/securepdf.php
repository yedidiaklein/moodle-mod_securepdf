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

$string['addsiteaddress'] = 'Add site name to image';
$string['addsiteaddress_explain'] = 'Add site name to each image of the PDF';
$string['addusername'] = 'Add username to each image';
$string['addusername_explain'] = 'Add username to each image of the PDF';
$string['allowdownload'] = 'Allow download original PDF to students';
$string['bottom'] = 'Bottom';
$string['cachedef_pages'] = 'Pages from PDF cache';
$string['downloadpdf'] = 'Download PDF';
$string['eventpage_view'] = 'Secure PDF page viewed';
$string['imagick_pdf_policy'] = 'You must set the policy of ImageMagick to allow PDF read. See https://stackoverflow.com/questions/52703123/override-default-imagemagick-policy-xml';
$string['imagickrequired'] = 'PHP Imagemagick extension is required';
$string['install_imagick'] = 'PHP-Imagick need to be installed, otherwise you and student won\'t be able to see the content';
$string['middle'] = 'Middle';
$string['modulename'] = 'Secure PDF';
$string['modulename_help'] = 'Use the securepdf module for adding PDF files securely to your course. The student won\'t be able to download the pdf, It will be shown to him as image for page - without right click to save image';
$string['modulenameplural'] = 'Secure PDFs';
$string['nocache'] = 'There is a problem with the cache or with cron - please contact the administrator';
$string['nocacheyet'] = 'No cache yet - Please wait...';
$string['nofile'] = 'No file found';
$string['nosuchpage'] = 'Error - No such page!';
$string['notallowedtodownload'] = 'You are not allowed to download the PDF';
$string['notapdf'] = 'The uploaded file is not a valid PDF document. Only real PDF files are accepted.';
$string['page'] = 'Page';
$string['pluginadministration'] = 'Secure PDF administration';
$string['pluginname'] = 'Secure PDF';
$string['resolution'] = 'Default resolution of image';
$string['resolution_explain'] = 'Set the resolution of image from PDF, as higher resolution you are using - the page will load slower';
$string['securepdf:addinstance'] = 'Add a new securepdf';
$string['securepdf:view'] = 'View securepdf';
$string['showall'] = 'Show all slides in one long page ';
$string['top'] = 'Top';
$string['usernameposition'] = 'Username and site name position';
$string['usernameposition_explain'] = 'Set the position of username and site name on the image';
