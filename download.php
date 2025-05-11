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
 * @package    mod_securepdf
 * @copyright  2020 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // Module id.
// get course id from module id.
$cm = get_coursemodule_from_id('securepdf', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/securepdf:view', $context);
$securepdfdata = $DB->get_record('securepdf', array('id' => $cm->instance), '*', MUST_EXIST);

if (!$securepdfdata->allowdownload) {
    print_error('notallowedtodownload', 'securepdf');
}
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_securepdf', 'content', 0, 'sortorder', false);
if (empty($files)) {
    print_error('nofiles', 'securepdf');
}
foreach ($files as $file) {
    if ($file->is_directory()) {
        continue;
    }
    $pdfcontent = $file->get_content();
    $filename = $file->get_filename();
    $filesize = $file->get_filesize();    
    break;
}
if (empty($pdfcontent)) {
    print_error('nofiles', 'securepdf');
}
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Transfer-Encoding: binary');
header('Content-Description: File Transfer');

echo $pdfcontent;
// Close the connection to the database.
$DB->close();
// Close the connection to the file storage.
$fs->close();

