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

namespace mod_securepdf;

#[\PHPUnit\Framework\Attributes\CoversClass(pdf_validator::class)]
/**
 * Tests for the PDF content validator.
 *
 * @package    mod_securepdf
 * @copyright  2026 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_securepdf\pdf_validator
 */
final class pdf_validator_test extends \advanced_testcase {
    /** @var string Minimal PostScript program. */
    const POSTSCRIPT = "%!PS-Adobe-3.0\n(pwned) print showpage\n";

    /** @var string Start of a minimal PDF. */
    const PDF = "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n";

    /**
     * Data provider for test_is_pdf_content.
     *
     * @return array
     */
    public static function content_provider(): array {
        return [
            'PDF 1.4' => [self::PDF, true],
            'PDF 1.7' => ["%PDF-1.7\n", true],
            'PDF 2.0' => ["%PDF-2.0\n", true],
            'PostScript' => [self::POSTSCRIPT, false],
            'EPS' => ["%!PS-Adobe-3.0 EPSF-3.0\n", false],
            'PostScript with PDF header later' => ["%!PS\n%PDF-1.4\n", false],
            'Leading space' => [' ' . self::PDF, false],
            'UTF-8 BOM' => ["\xEF\xBB\xBF" . self::PDF, false],
            'SVG' => ['<svg xmlns="http://www.w3.org/2000/svg"/>', false],
            'MVG' => ["push graphic-context\nviewbox 0 0 1 1\n", false],
            'Truncated header' => ['%PDF-', false],
            'Unknown version' => ['%PDF-9.9', false],
            'Empty' => ['', false],
        ];
    }

    /**
     * Header detection.
     *
     * @dataProvider content_provider
     * @param string $content
     * @param bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('content_provider')]
    public function test_is_pdf_content(string $content, bool $expected): void {
        $this->assertSame($expected, pdf_validator::is_pdf_content($content));
    }

    /**
     * Create a stored file in a test file area.
     *
     * @param string $filename
     * @param string $content
     * @return \stored_file
     */
    protected function create_file(string $filename, string $content): \stored_file {
        $fs = get_file_storage();
        return $fs->create_file_from_string([
            'contextid' => \context_system::instance()->id,
            'component' => 'mod_securepdf',
            'filearea' => 'test',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename,
        ], $content);
    }

    /**
     * Stored file checks: both extension and content must be PDF.
     */
    public function test_is_pdf_file(): void {
        $this->resetAfterTest();

        $this->assertTrue(pdf_validator::is_pdf_file($this->create_file('good.pdf', self::PDF)));
        $this->assertTrue(pdf_validator::is_pdf_file($this->create_file('UPPER.PDF', self::PDF)));
        $this->assertFalse(pdf_validator::is_pdf_file($this->create_file('renamed.pdf', self::POSTSCRIPT)));
        $this->assertFalse(pdf_validator::is_pdf_file($this->create_file('file.ps', self::PDF)));
        $this->assertFalse(pdf_validator::is_pdf_file($this->create_file('empty.pdf', '')));
    }

    /**
     * Non-PDF content never reaches Imagick.
     */
    public function test_read_into_imagick_rejects_postscript(): void {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('The imagick extension is not installed.');
        }
        $im = new \Imagick();
        $this->expectException(\moodle_exception::class);
        pdf_validator::read_into_imagick($im, self::POSTSCRIPT);
    }

    /**
     * Saved activity files are checked again and removed if they are not PDF.
     */
    public function test_validate_area_files(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/securepdf/locallib.php');
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $fs = get_file_storage();
        $record = [
            'contextid' => $context->id,
            'component' => 'mod_securepdf',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'doc.pdf',
        ];

        // A real PDF passes.
        $fs->create_file_from_string($record, self::PDF);
        \securepdf::validate_area_files($context);
        $this->assertFalse($fs->is_area_empty($context->id, 'mod_securepdf', 'content'));

        // PostScript renamed to .pdf is rejected and deleted.
        $fs->delete_area_files($context->id, 'mod_securepdf', 'content');
        $fs->create_file_from_string($record, self::POSTSCRIPT);
        try {
            \securepdf::validate_area_files($context);
            $this->fail('Expected moodle_exception');
        } catch (\moodle_exception $e) {
            $this->assertSame('notapdf', $e->errorcode);
        }
        $this->assertTrue($fs->is_area_empty($context->id, 'mod_securepdf', 'content'));
    }
}
