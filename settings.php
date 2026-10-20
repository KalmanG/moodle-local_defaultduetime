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
 * Admin settings for local_defaultduetime.
 *
 * @package    local_defaultduetime
 * @copyright  2026 Health Culture Foundation
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_defaultduetime', new lang_string('pluginname', 'local_defaultduetime'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_defaultduetime/enabled',
        new lang_string('enabled', 'local_defaultduetime'),
        new lang_string('enabled_desc', 'local_defaultduetime'),
        1
    ));

    $hours = [];
    for ($i = 0; $i < 24; $i++) {
        $hours[$i] = sprintf('%02d', $i);
    }
    $settings->add(new admin_setting_configselect(
        'local_defaultduetime/hour',
        new lang_string('hour', 'local_defaultduetime'),
        new lang_string('hour_desc', 'local_defaultduetime'),
        18,
        $hours
    ));

    $minutes = [0 => '00', 15 => '15', 30 => '30', 45 => '45'];
    $settings->add(new admin_setting_configselect(
        'local_defaultduetime/minute',
        new lang_string('minute', 'local_defaultduetime'),
        new lang_string('minute_desc', 'local_defaultduetime'),
        0,
        $minutes
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_defaultduetime/fields',
        new lang_string('fields', 'local_defaultduetime'),
        new lang_string('fields_desc', 'local_defaultduetime'),
        \local_defaultduetime\form_adjuster::DEFAULT_FIELDS,
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_defaultduetime/shownote',
        new lang_string('shownote', 'local_defaultduetime'),
        new lang_string('shownote_desc', 'local_defaultduetime'),
        1
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_defaultduetime/notetext',
        new lang_string('notetext', 'local_defaultduetime'),
        new lang_string('notetext_desc', 'local_defaultduetime'),
        '',
        PARAM_RAW
    ));
}
