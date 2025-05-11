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
 * Prints a particular instance of securepdf
 *
 * @package    mod_securepdf
 * @copyright  2020 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id = required_param('id', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

// Counter for reload.
// This is used for the one page view when cache is not yet created.
$counter = optional_param('counter', 0, PARAM_INT);

$settings = get_config('securepdf');

$cm = get_coursemodule_from_id('securepdf', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$context = context_module::instance($cm->id);
$securepdf = new securepdf($context, $cm, $course);

require_login($course, true, $cm);
require_capability('mod/securepdf:view', $context);

$PAGE->set_pagelayout('incourse');

$url = new moodle_url('/mod/securepdf/view.php', array('id' => $id));
$PAGE->set_url('/mod/securepdf/view.php', array('id' => $cm->id));

if (!securepdf::check_imagick()) {
    echo $OUTPUT->header();
    echo $OUTPUT->footer();
    die();
}

// Check if we want all pages in one long page.
// Get data from securepdf table.
$securepdfdata = $DB->get_record('securepdf', array('id' => $securepdf->get_instance()->id), '*', MUST_EXIST);
$onepageview = $securepdfdata->onepageview;
if ($onepageview) {
    echo $OUTPUT->header();

    // check if we have to provide a download link of pdf
    if ($securepdfdata->allowdownload) {
        $downloadurl = $CFG->wwwroot . '/mod/securepdf/download.php?id=' . $id;
        // Create PDF icon using FontAwesome.
        $icon = '<i class="fa fa-file-pdf-o" aria-hidden="true" style="font-size: 36px;"></i>';
        // Show PDF icon and link to download the PDF
        echo html_writer::link($downloadurl, $icon . ' ' . get_string('downloadpdf', 'mod_securepdf'), ['target' => '_blank']);
    }

    $cached = \mod_securepdf\view::checkcache($cm, 0);
    $numpages = $cached['numpages'];
    if (!$numpages) { // No cache - Get page num only.
        echo '<br><br>' . get_string('nocacheyet', 'mod_securepdf');
        // Refresh every minutes.
        $PAGE->requires->js_call_amd('mod_securepdf/reload', 'init', ['counter']);
        // Adhoc task for generating the cache of all pages
        // This situation happen while cache was purged
        $adhoccache = new \mod_securepdf\task\create_cache();
        $adhoccache->set_custom_data(['moduleid' => $cm->id]);
        \core\task\manager::queue_adhoc_task($adhoccache);
    } else {
        for ($i = 0; $i < $numpages; $i++) {
            $page = $i;
            $cached = \mod_securepdf\view::checkcache($cm, $page);
            $data = $cached['data'];
            if (!$data) { // If there is not yet cache for this page.
                echo '<br><br>' . get_string('nocacheyet', 'mod_securepdf');
                // Refresh every minutes.
                if ($counter < 3) {
                    $PAGE->requires->js_call_amd('mod_securepdf/reload', 'init', ['counter']);
                } else if ($counter < 4) { // after 3 times - stop reloading and run the adhoc task
                    // Adhoc task for generating the cache of all pages
                    $adhoccache = new \mod_securepdf\task\create_cache();
                    $adhoccache->set_custom_data(['moduleid' => $cm->id]);
                    \core\task\manager::queue_adhoc_task($adhoccache);
                } else {
                    echo '<br><br>' . get_string('nocache', 'mod_securepdf');
                }
                break;
            }
            // Add watermark to image.
            $data = \mod_securepdf\view::addwatermark($data, $settings);
            echo $OUTPUT->render_from_template('mod_securepdf/singleformulti',
            [   'base64' => $data,
                'page' => $page,
            ]);
        }
    }
} else {
    // Each slide is shown in a separate page.

    // Update page views in table - in order to be able to set completion.
    $pageview = ['module' => $cm->id,
                'userid' => $USER->id,
                'page' => $page
                ];
    $exist = $DB->get_record('securepdf_pageviews', $pageview);
    if ($exist) {
        $pageview['timemodified'] = time();
        $pageview['id'] = $exist->id;
        $DB->update_record('securepdf_pageviews', $pageview);
    } else {
        $pageview['timemodified'] = time();
        $pageview['timecreated'] = time();
        $DB->insert_record('securepdf_pageviews', $pageview);
    }

    $event = \mod_securepdf\event\page_view::create(array(
        'objectid' => $securepdf->get_instance()->id,
        'context' => context_module::instance($cm->id),
        'other' => $page + 1
    ));
    $event->trigger();

    $cached = \mod_securepdf\view::checkcache($cm, $page);
    $data = $cached['data'];
    $numpages = $cached['numpages'];

    // If there is no cache - we should parse the PDF and write cache.
    if (!$data || !$numpages) {
        // First call the adhoc task for generating the cache of all pages
        // This situation happen while cache was purged
        // otherwise the cache is created on create/update resource.
        $adhoccache = new \mod_securepdf\task\create_cache();
        $adhoccache->set_custom_data(['moduleid' => $cm->id]);
        \core\task\manager::queue_adhoc_task($adhoccache);

        $numpagesdata = \mod_securepdf\view::getnumpages($context, $settings->resolution, $cm, $page);
        $numpages = $numpagesdata['numpages'];
        $bas64 = $numpagesdata['data'];

        if ($page > $numpages) {
            $error = get_string('nosuchpage', 'mod_securepdf');
        }
    } else {
        // Get image from cache.
        $base64 = $data;
    }

    // Update 'viewed' state if required by completion system.
    // It's here and not in top of this file because we need the total number of pages in this PDF.
    $completion = new completion_info($course);
    // Check if user viewed all pages.
    $allpages = $DB->count_records('securepdf_pageviews', ['module' => $cm->id, 'userid' => $USER->id]);
    if ($allpages == $numpages) {
        $completion->set_module_viewed($cm);
    }

    echo $OUTPUT->header();

    // check if we have to provide a download link of pdf
    if ($securepdfdata->allowdownload) {
        $downloadurl = $CFG->wwwroot . '/mod/securepdf/download.php?id=' . $id;
        // Create PDF icon using FontAwesome.
        $icon = '<i class="fa fa-file-pdf-o" aria-hidden="true" style="font-size: 36px;"></i>';
        // Show PDF icon and link to download the PDF
        echo html_writer::link($downloadurl, $icon . ' ' . get_string('downloadpdf', 'mod_securepdf'), ['target' => '_blank']);
    }

    $pages = [];
    for ($i = 0; $i < $numpages; $i++) {
        $pages[$i]['url'] = $CFG->wwwroot . '/mod/securepdf/view.php?id=' . $id . '&page=' . $i;
        $pages[$i]['page'] = $i + 1;
    }

    $next = 0;
    if (($page + 1) < $numpages) {
        $next = $page + 1;
    }

    $nexturl = $CFG->wwwroot . '/mod/securepdf/view.php?id=' . $id . '&page=' . $next;
    $previousurl = $CFG->wwwroot . '/mod/securepdf/view.php?id=' . $id . '&page=' . ($page - 1);

    // Add watermark to image.
    $base64 = \mod_securepdf\view::addwatermark($base64, $settings);

    echo $OUTPUT->render_from_template('mod_securepdf/imageview',
        [   'base64' => $base64,
            'page' => $page + 1,
            'total' => $numpages,
            'pages' => $pages,
            'next' => $next,
            'previous' => $page,
            'nexturl' => $nexturl,
            'previousurl' => $previousurl
            ]);
}

echo $OUTPUT->footer();
