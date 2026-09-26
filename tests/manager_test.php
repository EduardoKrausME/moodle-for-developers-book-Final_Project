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

namespace mod_checkpoint;

use mod_checkpoint\completion\custom_completion;
use mod_checkpoint\event\submission_created;
use mod_checkpoint\local\manager;
use mod_checkpoint\local\submission_status;

/**
 * Tests for the checkpoint application service and Moodle projections.
 *
 * @covers     \mod_checkpoint\local\manager
 * @package    mod_checkpoint
 * @category   test
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manager_test extends \advanced_testcase {
    /**
     * A learner can submit and the domain event contains the expected identifiers.
     *
     * @return void
     */
    public function test_student_can_submit_and_event_is_triggered(): void {
        $this->resetAfterTest();
        $fixture = $this->create_fixture();
        $student = $fixture[1];
        $checkpoint = $fixture[3];
        $this->setUser($student);

        $sink = $this->redirectEvents();
        $id = \core\di::get(manager::class)->submit($checkpoint->id, $student->id, [
            'submissiontext' => ['text' => 'My evidence', 'format' => FORMAT_HTML],
        ]);

        $this->assertGreaterThan(0, $id);
        $record = $this->get_submission($id);
        $this->assertSame(submission_status::SUBMITTED, $record->status);
        $this->assertSame('My evidence', $record->submissiontext);

        $events = array_values(array_filter(
            $sink->get_events(),
            static fn($event): bool => $event instanceof submission_created,
        ));
        $this->assertCount(1, $events);
        $this->assertSame($id, (int)$events[0]->objectid);
        $this->assertSame((int)$student->id, (int)$events[0]->relateduserid);
    }

    /**
     * A user without the grading capability cannot grade by calling the service directly.
     *
     * @return void
     */
    public function test_user_without_grade_capability_cannot_grade(): void {
        $this->resetAfterTest();
        $fixture = $this->create_fixture();
        $student = $fixture[1];
        $checkpoint = $fixture[3];
        $manager = \core\di::get(manager::class);

        $this->setUser($student);
        $submissionid = $manager->submit($checkpoint->id, $student->id, [
            'submissiontext' => ['text' => 'Evidence', 'format' => FORMAT_HTML],
        ]);

        $this->expectException(\required_capability_exception::class);
        $manager->grade($submissionid, $student->id, 80, 'Not allowed');
    }

    /**
     * Grading updates both the domain record and the Moodle gradebook.
     *
     * @return void
     */
    public function test_grade_updates_gradebook(): void {
        global $CFG;
        $this->resetAfterTest();
        require_once($CFG->libdir . '/gradelib.php');

        [$course, $student, $teacher, $checkpoint] = $this->create_fixture();
        $manager = \core\di::get(manager::class);
        $this->setUser($student);
        $submissionid = $manager->submit($checkpoint->id, $student->id, [
            'submissiontext' => ['text' => 'Evidence', 'format' => FORMAT_HTML],
        ]);

        $this->setUser($teacher);
        $manager->grade($submissionid, $teacher->id, 87.5, 'Good work');

        $record = $this->get_submission($submissionid);
        $this->assertSame(submission_status::GRADED, $record->status);
        $this->assertEquals(87.5, $record->grade);

        $grades = grade_get_grades($course->id, 'mod', 'checkpoint', $checkpoint->id, $student->id);
        $this->assertArrayHasKey($student->id, $grades->items[0]->grades);
        $this->assertEquals(87.5, $grades->items[0]->grades[$student->id]->grade);
    }

    /**
     * Custom completion follows submission and grading domain state.
     *
     * @return void
     */
    public function test_custom_completion_rules(): void {
        $this->resetAfterTest();
        [$course, $student, $teacher, $checkpoint] = $this->create_fixture(true);
        $manager = \core\di::get(manager::class);

        $this->setUser($student);
        $submissionid = $manager->submit($checkpoint->id, $student->id, [
            'submissiontext' => ['text' => 'Evidence', 'format' => FORMAT_HTML],
        ]);

        get_fast_modinfo($course, 0, true);
        $cm = get_fast_modinfo($course)->get_cm(get_coursemodule_from_instance('checkpoint', $checkpoint->id)->id);
        $completion = new custom_completion($cm, $student->id);
        $this->assertSame(COMPLETION_COMPLETE, $completion->get_state('completionsubmit'));
        $this->assertSame(COMPLETION_INCOMPLETE, $completion->get_state('completiongrade'));

        $this->setUser($teacher);
        $manager->grade($submissionid, $teacher->id, 90, 'Complete');

        get_fast_modinfo($course, 0, true);
        $cm = get_fast_modinfo($course)->get_cm($cm->id);
        $completion = new custom_completion($cm, $student->id);
        $this->assertSame(COMPLETION_COMPLETE, $completion->get_state('completiongrade'));
    }

    /**
     * Build a course, learner, teacher and checkpoint.
     *
     * @param bool $completion Whether to enable custom completion rules.
     * @return array
     */
    private function create_fixture(bool $completion = false): array {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => $completion ? 1 : 0]);
        $student = $generator->create_and_enrol($course, 'student');
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $record = [
            'course' => $course->id,
            'completionsubmit' => $completion ? 1 : 0,
            'completiongrade' => $completion ? 1 : 0,
        ];
        if ($completion) {
            $record['completion'] = COMPLETION_TRACKING_AUTOMATIC;
        }
        $checkpoint = $generator->get_plugin_generator('mod_checkpoint')->create_instance($record);
        return [$course, $student, $teacher, $checkpoint];
    }

    /**
     * Fetch one submission record.
     *
     * @param int $id Submission id.
     * @return \stdClass
     */
    private function get_submission(int $id): \stdClass {
        global $DB;
        return $DB->get_record('checkpoint_submission', ['id' => $id], '*', MUST_EXIST);
    }
}
