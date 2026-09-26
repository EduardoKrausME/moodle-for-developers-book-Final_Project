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

namespace mod_checkpoint\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use mod_checkpoint\local\manager;

/**
 * Privacy provider tests.
 *
 * @package    mod_checkpoint
 * @category   test
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privacy_provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * Metadata describes the submission table and the Files API link.
     *
     * @return void
     */
    public function test_get_metadata(): void {
        $collection = provider::get_metadata(new collection('mod_checkpoint'));
        $items = $collection->get_collection();
        $this->assertGreaterThanOrEqual(2, count($items));
    }

    /**
     * Context discovery, export and deletion cover the learner submission.
     *
     * @return void
     */
    public function test_context_export_and_delete(): void {
        global $DB;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $student = $generator->create_and_enrol($course, 'student');
        $checkpoint = $generator->get_plugin_generator('mod_checkpoint')->create_instance([
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionsubmit' => 1,
        ]);
        $cm = get_coursemodule_from_instance('checkpoint', $checkpoint->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $this->setUser($student);
        $submissionid = \core\di::get(manager::class)->submit($checkpoint->id, $student->id, [
            'submissiontext' => ['text' => 'Private evidence', 'format' => FORMAT_HTML],
        ]);

        get_fast_modinfo($course, 0, true);
        $cminfo = get_fast_modinfo($course)->get_cm($cm->id);
        $completion = new \completion_info($course);
        $this->assertSame(
            COMPLETION_COMPLETE,
            (int)$completion->get_data($cminfo, false, $student->id)->completion,
        );

        $contextlist = provider::get_contexts_for_userid($student->id);
        $this->assertCount(1, $contextlist);
        $this->assertSame($context->id, $contextlist->current()->id);

        $this->export_context_data_for_user($student->id, $context, 'mod_checkpoint');
        $this->assertTrue(writer::with_context($context)->has_any_data());

        $approved = new approved_contextlist($student, 'mod_checkpoint', [$context->id]);
        provider::delete_data_for_user($approved);
        $this->assertFalse($DB->record_exists('checkpoint_submission', ['id' => $submissionid]));

        get_fast_modinfo($course, 0, true);
        $cminfo = get_fast_modinfo($course)->get_cm($cm->id);
        $completion = new \completion_info($course);
        $this->assertSame(
            COMPLETION_INCOMPLETE,
            (int)$completion->get_data($cminfo, false, $student->id)->completion,
        );
    }
}
