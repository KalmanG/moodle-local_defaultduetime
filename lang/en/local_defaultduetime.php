<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * English strings for local_defaultduetime.
 *
 * @package    local_defaultduetime
 * @copyright  2026 Health Culture Foundation
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['enabled'] = 'Set a default deadline time';
$string['enabled_desc'] = 'When a teacher creates an activity, its deadline fields start at the time below instead of midnight. Saved activities are never changed.';
$string['fields'] = 'Deadline fields';
$string['fields_desc'] = 'One "module:field" per line. Only these date fields get the default time.';
$string['hour'] = 'Hour';
$string['hour_desc'] = 'Hour of the default deadline, on a 24-hour clock. 18 is 6:00 PM.';
$string['minute'] = 'Minute';
$string['minute_desc'] = 'Minute of the default deadline.';
$string['notedefault'] = 'Deadlines on this site start at **{$a}**, not midnight, so students can finish their work and still get a full night\'s sleep. You can still pick any time.';
$string['notetext'] = 'Note text';
$string['notetext_desc'] = 'Shown to teachers above the deadline fields. Markdown is allowed and {time} is replaced with the default time. Leave empty to use the standard text.';
$string['pluginname'] = 'Default deadline time';
$string['privacy:metadata'] = 'The Default deadline time plugin does not store any personal data.';
$string['shownote'] = 'Show a note to teachers';
$string['shownote_desc'] = 'Show a short note above the deadline fields that explains the default time.';
