<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint;

use mod_checkpoint\local\manager;

/**
 * Tests for protected evidence file delivery.
 *
 * @package    mod_checkpoint
 * @category   test
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class pluginfile_test extends \advanced_testcase {
    /**
     * A learner cannot access another learner's evidence by changing only the item id.
     *
     * @return void
     */
    public function test_student_cannot_access_another_students_evidence_itemid(): void {
        global $CFG;

        $this->resetAfterTest();
        require_once($CFG->dirroot . '/mod/checkpoint/lib.php');

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $owner = $generator->create_and_enrol($course, 'student');
        $attacker = $generator->create_and_enrol($course, 'student');
        $checkpoint = $generator->get_plugin_generator('mod_checkpoint')->create_instance([
            'course' => $course->id,
        ]);
        $cm = get_coursemodule_from_instance('checkpoint', $checkpoint->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $this->setUser($owner);
        $submissionid = \core\di::get(manager::class)->submit($checkpoint->id, $owner->id, [
            'submissiontext' => ['text' => 'Private evidence', 'format' => FORMAT_HTML],
        ]);

        $this->setUser($attacker);
        $this->assertFalse(checkpoint_pluginfile(
            $course,
            $cm,
            $context,
            'evidence',
            [$submissionid, 'evidence.txt'],
            true,
        ));
    }
}
