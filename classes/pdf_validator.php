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

/**
 * Validates that uploaded content is really a PDF before it reaches ImageMagick.
 *
 * ImageMagick chooses its decoder from the first bytes of a blob, not from the
 * file name. Without this check, a PostScript (or EPS, SVG, MVG, ...) file
 * renamed to .pdf would be handed to the matching decoder (for PostScript:
 * the Ghostscript PS interpreter), which has a history of remote code
 * execution bugs.
 *
 * @package    mod_securepdf
 * @copyright  2026 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_validator {
    /** @var int Number of bytes needed to check the header. */
    const HEADER_BYTES = 16;

    /**
     * Check whether the given bytes start with a valid PDF header.
     *
     * The header has to be at offset 0 ("%PDF-1.x" / "%PDF-2.x"). Leading junk is
     * rejected on purpose: some ImageMagick decoders look for their own magic
     * bytes there, and a strict check stops polyglot files.
     *
     * @param string $content File content (or at least its first bytes).
     * @return bool
     */
    public static function is_pdf_content(string $content): bool {
        return (bool) preg_match('/^%PDF-[12]\.\d/', substr($content, 0, self::HEADER_BYTES));
    }

    /**
     * Check a stored file: .pdf extension and a real PDF header.
     *
     * Only the first bytes are read, so large files are not loaded into memory.
     *
     * @param \stored_file $file
     * @return bool
     */
    public static function is_pdf_file(\stored_file $file): bool {
        if ($file->is_directory()) {
            return false;
        }
        if (strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) !== 'pdf') {
            return false;
        }
        $fh = $file->get_content_file_handle();
        if (!$fh) {
            return false;
        }
        $header = fread($fh, self::HEADER_BYTES);
        fclose($fh);
        return $header !== false && self::is_pdf_content($header);
    }

    /**
     * Load PDF content into an Imagick object safely.
     *
     * The header check is the real protection: Ghostscript runs a PostScript
     * blob even when ImageMagick is told it is a PDF, but a blob that starts
     * with "%PDF-" is handled by the PDF interpreter only. Setting the format
     * also stops ImageMagick from picking any other decoder (SVG, MVG, MSL...).
     *
     * @param \Imagick $im Imagick object (resolution should already be set).
     * @param string $content PDF content.
     * @throws \moodle_exception If the content is not a PDF.
     */
    public static function read_into_imagick(\Imagick $im, string $content): void {
        if (!self::is_pdf_content($content)) {
            throw new \moodle_exception('notapdf', 'mod_securepdf');
        }
        // With the format set, ImageMagick skips magic-byte detection.
        $im->setFormat('PDF');
        $im->readImageBlob($content);
    }
}
