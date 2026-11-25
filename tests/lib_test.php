<?php
// This file is part of Moodle - http://moodle.org/
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// Moodle is distributed without any warranty.
// See <https://www.gnu.org/licenses/>.

namespace mod_securepdf;

/**
 * Feature support dispatch regression tests.
 * @package mod_securepdf
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    public function test_supported_features_and_unknown_feature(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/securepdf/lib.php');
        $features = [
            FEATURE_GRADE_HAS_GRADE => false,
            FEATURE_GRADE_OUTCOMES => false,
            FEATURE_COMPLETION_TRACKS_VIEWS => true,
            FEATURE_GROUPS => false,
            FEATURE_GROUPINGS => false,
            FEATURE_MOD_ARCHETYPE => MOD_ARCHETYPE_RESOURCE,
            FEATURE_MOD_INTRO => true,
            FEATURE_BACKUP_MOODLE2 => true,
            FEATURE_SHOW_DESCRIPTION => true,
        ];
        foreach ($features as $feature => $expected) {
            $this->assertSame($expected, securepdf_supports($feature));
        }
        $this->assertNull(securepdf_supports('mod_securepdf_unknown_feature'));
        // Core upgrade rejects plugins that still declare this obsolete feature.
        $this->assertNull(securepdf_supports(FEATURE_GROUPMEMBERSONLY));
    }
}
