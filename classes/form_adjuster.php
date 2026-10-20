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

namespace local_defaultduetime;

use MoodleQuickForm;

/**
 * Moves the default deadline on new activities to a set time of day (6:00 PM out of the box).
 *
 * @package    local_defaultduetime
 * @copyright  2026 Health Culture Foundation
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_adjuster {
    /** @var string Deadline fields adjusted out of the box, one "module:field" per line. */
    const DEFAULT_FIELDS = "assign:duedate\nassign:cutoffdate\nquiz:timeclose\nforum:duedate\nforum:cutoffdate\n" .
        "lesson:deadline\nchoice:timeclose\nfeedback:timeclose\nworkshop:submissionend\nworkshop:assessmentend\n" .
        "scorm:timeclose\ndata:timeavailableto";

    /** @var string Name of the note element added to the form. */
    const NOTE_ELEMENT = 'local_defaultduetime_note';

    /**
     * Applies the default deadline time and the faculty note to an activity settings form.
     *
     * @param \moodleform_mod $formwrapper The activity settings form.
     * @param MoodleQuickForm $mform The underlying quickform.
     */
    public static function apply($formwrapper, MoodleQuickForm $mform): void {
        $config = get_config('local_defaultduetime');
        if (empty($config->enabled)) {
            return;
        }

        $fields = self::fields_for_module(self::get_modname($formwrapper), $config->fields ?? self::DEFAULT_FIELDS);
        $fields = array_values(array_filter($fields, function (string $field) use ($mform): bool {
            return $mform->elementExists($field) && $mform->getElement($field)->getType() === 'date_time_selector';
        }));
        if (!$fields) {
            return;
        }

        $hour = (int)($config->hour ?? 18);
        $minute = (int)($config->minute ?? 0);

        // Only new activities get the new default. Saved deadlines, and anything the teacher just submitted, stay as they are.
        $isnew = empty($formwrapper->get_coursemodule());
        if ($isnew && !$mform->isSubmitted()) {
            foreach ($fields as $field) {
                self::adjust_field($mform, $field, $hour, $minute);
            }
        }

        if (!empty($config->shownote) && !$mform->elementExists(self::NOTE_ELEMENT)) {
            $note = $mform->createElement('static', self::NOTE_ELEMENT, '', self::note_html($config, $hour, $minute));
            $mform->insertElementBefore($note, $fields[0]);
        }
    }

    /**
     * Sets the time of day on one date_time_selector default.
     *
     * A field that already has a default date keeps its date and gets the new time. A field that starts switched off
     * gets today's date at the new time, so ticking "Enable" shows that time instead of the current clock time.
     *
     * @param MoodleQuickForm $mform The form.
     * @param string $field Element name.
     * @param int $hour Hour of day, 0-23.
     * @param int $minute Minute, 0-59.
     */
    public static function adjust_field(MoodleQuickForm $mform, string $field, int $hour, int $minute): void {
        $current = $mform->_defaultValues[$field] ?? 0;
        if (is_array($current)) {
            return;
        }
        $current = (int)$current;

        if ($current > 0) {
            $mform->setDefault($field, self::set_time_of_day($current, $hour, $minute));
            return;
        }

        // An array without 'enabled' keeps an optional selector switched off but pre-fills its dropdowns.
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $date = $calendar->timestamp_to_date_array(self::set_time_of_day(time(), $hour, $minute, false), 99);
        $mform->setDefault($field, [
            'day' => $date['mday'],
            'month' => $date['mon'],
            'year' => $date['year'],
            'hour' => $hour,
            'minute' => $minute,
        ]);
    }

    /**
     * Returns the timestamp moved to the given time of day, in the user's timezone.
     *
     * With $neverlater set, the result is the latest such time at or before $timestamp, so a default deadline is only
     * ever moved earlier. Moodle's stock default of 00:00 on day 7 becomes 6:00 PM on day 6, the evening teachers mean.
     *
     * @param int $timestamp Unix timestamp.
     * @param int $hour Hour of day, 0-23.
     * @param int $minute Minute, 0-59.
     * @param bool $neverlater Step back a day if the new time would be later than the original.
     * @param \DateTimeZone|null $timezone Timezone to use, defaults to the current user's.
     * @return int
     */
    public static function set_time_of_day(
        int $timestamp,
        int $hour,
        int $minute,
        bool $neverlater = true,
        ?\DateTimeZone $timezone = null
    ): int {
        $timezone = $timezone ?? \core_date::get_user_timezone_object();
        $date = new \DateTime('@' . $timestamp);
        $date->setTimezone($timezone);
        $date->setTime($hour, $minute, 0);
        if ($neverlater && $date->getTimestamp() > $timestamp) {
            $date->modify('-1 day');
            $date->setTime($hour, $minute, 0);
        }
        return $date->getTimestamp();
    }

    /**
     * Returns the field names configured for one module.
     *
     * @param string $modname Module name, e.g. "assign".
     * @param string $setting The "module:field" lines from the plugin settings.
     * @return string[]
     */
    public static function fields_for_module(string $modname, string $setting): array {
        $fields = [];
        foreach (preg_split('/[\r\n,]+/', $setting) as $line) {
            $parts = array_map('trim', explode(':', $line, 2));
            if (count($parts) === 2 && $parts[0] === $modname && $parts[1] !== '') {
                $fields[] = $parts[1];
            }
        }
        return array_values(array_unique($fields));
    }

    /**
     * Works out which module a settings form belongs to.
     *
     * @param \moodleform_mod $formwrapper The form.
     * @return string
     */
    protected static function get_modname($formwrapper): string {
        $current = $formwrapper->get_current();
        if (!empty($current->modulename)) {
            return $current->modulename;
        }
        if (preg_match('/^mod_([a-z0-9_]+)_mod_form$/', get_class($formwrapper), $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Builds the note shown to teachers above the first deadline field.
     *
     * @param \stdClass $config Plugin config.
     * @param int $hour Hour of day.
     * @param int $minute Minute.
     * @return string HTML.
     */
    protected static function note_html(\stdClass $config, int $hour, int $minute): string {
        $time = userdate(self::set_time_of_day(time(), $hour, $minute, false), get_string('strftimetime', 'langconfig'));
        $text = trim($config->notetext ?? '');
        $text = $text === '' ? get_string('notedefault', 'local_defaultduetime', $time) : str_replace('{time}', $time, $text);
        return \html_writer::div(
            format_text($text, FORMAT_MARKDOWN, ['context' => \context_system::instance()]),
            'alert alert-info local-defaultduetime-note'
        );
    }
}
