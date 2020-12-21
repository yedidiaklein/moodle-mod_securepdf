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
$string['modulename'] = 'PDF מאובטח';
$string['modulenameplural'] = 'PDF מאובטחים';
$string['modulename_help'] = 'השתמשו במודול PDF מאובטח כדי להגן על חומרי הלמידה שלכם, התלמיד לא יוכל להוריד את ה-PDF אלא רק יוצג לו עמוד בתצורת תמונה בכל צפייה בעמוד ללא אפשרות מקש ימני ושמירת התמונה.';

$string['securepdf:addinstance'] = 'הוספת PDF מאובטח חדש';
$string['securepdf:view'] = 'תצוגת PDF מאובטח';

$string['pluginadministration'] = 'ניהול מנגנון PDF מאובטח';
$string['pluginname'] = 'PDF מאובטח';

$string['eventpage_view'] = 'נצפה עמוד מרכיב PDF מאובטח';

$string['resolution'] = 'ברירת מחדל לרזולוציה של תמונה';
$string['resolution_explain'] = 'דרך משתנה זה ניתן לשלוט באיכות התמונה שנוצרת מתוך ה-PDF, שימו לב שאם יש פה רזולוציה גבוהה - טעינת העמוד לצפייה איטית יותר.';

$string['page'] = 'עמוד';
$string['nosuchpage'] = 'שגיאה - אין עמוד כזה...';
$string['install_imagick'] = 'נדרש להתקין רכיב PHP-Imagick - אחרת אתה והתלמידים לא תוכלו לצפות בתוכן';
$string['imagick_pdf_policy'] = 'יש לאפשר את ה-policy עבור קריאת PDF ב-ImageMagick - ראו https://stackoverflow.com/questions/52703123/override-default-imagemagick-policy-xml';
$string['cachedef_pages'] = 'מטמוני הדפים מתוך ה-PDF';
$string['imagickrequired'] = 'רכיב PHP Imagemagick מוכרח להיות מותקן';