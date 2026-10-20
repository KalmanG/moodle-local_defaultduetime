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
 * Callbacks for local_defaultduetime.
 *
 * @package    local_defaultduetime
 * @copyright  2026 Health Culture Foundation
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Sets the default time of day on activity deadline fields, after the form data is loaded.
 *
 * Called by moodleform_mod::plugin_extend_coursemodule_definition_after_data().
 *
 * @param moodleform_mod $formwrapper The activity settings form.
 * @param MoodleQuickForm $mform The underlying quickform.
 */
function local_defaultduetime_coursemodule_definition_after_data($formwrapper, $mform): void {
    \local_defaultduetime\form_adjuster::apply($formwrapper, $mform);
}
