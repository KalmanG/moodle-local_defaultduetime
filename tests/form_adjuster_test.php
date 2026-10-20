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

/**
 * Tests for the deadline form adjuster.
 *
 * @package    local_defaultduetime
 * @copyright  2026 Health Culture Foundation
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_defaultduetime\form_adjuster
 */
final class form_adjuster_test extends \advanced_testcase {
    public function test_set_time_of_day_moves_midnight_to_previous_evening(): void {
        $tz = new \DateTimeZone('America/New_York');
        $midnight = (new \DateTime('2026-10-26 00:00:00', $tz))->getTimestamp();
        $expected = (new \DateTime('2026-10-25 18:00:00', $tz))->getTimestamp();
        $this->assertSame($expected, form_adjuster::set_time_of_day($midnight, 18, 0, true, $tz));
    }

    public function test_set_time_of_day_never_moves_later(): void {
        $tz = new \DateTimeZone('Australia/Perth');
        $morning = (new \DateTime('2026-10-26 09:30:00', $tz))->getTimestamp();
        $result = form_adjuster::set_time_of_day($morning, 18, 0, true, $tz);
        $this->assertLessThanOrEqual($morning, $result);
        $this->assertSame('2026-10-25 18:00', (new \DateTime('@' . $result))->setTimezone($tz)->format('Y-m-d H:i'));
    }

    public function test_set_time_of_day_same_day_when_earlier(): void {
        $tz = new \DateTimeZone('UTC');
        $evening = (new \DateTime('2026-10-26 23:59:00', $tz))->getTimestamp();
        $this->assertSame(
            (new \DateTime('2026-10-26 17:00:00', $tz))->getTimestamp(),
            form_adjuster::set_time_of_day($evening, 17, 0, true, $tz)
        );
    }

    public function test_fields_for_module(): void {
        $setting = "assign:duedate\n assign : cutoffdate \nquiz:timeclose\nbroken line\nassign:duedate,forum:duedate";
        $this->assertSame(['duedate', 'cutoffdate'], form_adjuster::fields_for_module('assign', $setting));
        $this->assertSame(['timeclose'], form_adjuster::fields_for_module('quiz', $setting));
        $this->assertSame([], form_adjuster::fields_for_module('lesson', $setting));
    }

    public function test_adjust_field_with_default_date(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $mform = $this->make_form();
        $start = make_timestamp(2026, 11, 2, 0, 0);
        $mform->setDefault('duedate', $start);

        form_adjuster::adjust_field($mform, 'duedate', 18, 0);

        $value = $mform->getElement('duedate')->getValue();
        $this->assertEquals(18, $value['hour'][0]);
        $this->assertEquals(0, $value['minute'][0]);
        $this->assertEquals(1, $value['day'][0]);
        $this->assertSame(make_timestamp(2026, 11, 1, 18, 0), $mform->_defaultValues['duedate']);
    }

    public function test_adjust_field_when_disabled_prefills_time_only(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $mform = $this->make_form();
        $mform->setDefault('duedate', 0);

        form_adjuster::adjust_field($mform, 'duedate', 18, 0);

        $value = $mform->getElement('duedate')->getValue();
        $this->assertEquals(18, $value['hour'][0]);
        $this->assertEquals(0, $value['minute'][0]);
        $this->assertIsArray($mform->_defaultValues['duedate']);
        $this->assertArrayNotHasKey('enabled', $mform->_defaultValues['duedate']);
    }

    /**
     * Builds a bare form with one optional deadline field.
     *
     * @return \MoodleQuickForm
     */
    private function make_form(): \MoodleQuickForm {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        $mform = new \MoodleQuickForm('test', 'post', '');
        $mform->addElement('date_time_selector', 'duedate', 'Due', ['optional' => true]);
        return $mform;
    }
}
