<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint;

use mod_checkpoint\local\manager;
use mod_checkpoint\task\send_grade_notification;

/**
 * Tests for asynchronous grade notifications.
 *
 * @package    mod_checkpoint
 * @category   test
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class task_test extends \advanced_testcase {
    /**
     * Running the notification task twice sends one message.
     *
     * @return void
     */
    public function test_task_is_idempotent_after_success(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('notifygrade', 0, 'mod_checkpoint');

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_and_enrol($course, 'student');
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $checkpoint = $generator->get_plugin_generator('mod_checkpoint')->create_instance(['course' => $course->id]);
        $manager = \core\di::get(manager::class);

        $this->setUser($student);
        $submissionid = $manager->submit($checkpoint->id, $student->id, [
            'submissiontext' => ['text' => 'Evidence', 'format' => FORMAT_HTML],
        ]);
        $this->setUser($teacher);
        $manager->grade($submissionid, $teacher->id, 75, 'Feedback');

        $sink = $this->redirectMessages();
        $task = new send_grade_notification();
        $task->set_custom_data(['submissionid' => $submissionid]);
        $task->execute();
        $task->execute();

        $this->assertCount(1, $sink->get_messages());
        $this->assertGreaterThan(0, (int)$DB->get_field('checkpoint_submission', 'notificationtime', ['id' => $submissionid]));
    }
}
