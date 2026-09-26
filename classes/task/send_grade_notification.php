<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint\task;

use mod_checkpoint\local\submission_status;

/**
 * Send a grade notification outside the teacher grading request.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class send_grade_notification extends \core\task\adhoc_task {
    /**
     * Execute the queued notification.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;

        $data = $this->get_custom_data();
        $submissionid = (int)($data->submissionid ?? 0);
        if (!$submissionid) {
            return;
        }

        $submission = $DB->get_record('checkpoint_submission', ['id' => $submissionid]);
        if (!$submission || $submission->status !== submission_status::GRADED || $submission->notificationtime > 0) {
            return;
        }

        $checkpoint = $DB->get_record('checkpoint', ['id' => $submission->checkpointid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('checkpoint', $checkpoint->id, $checkpoint->course, false, MUST_EXIST);
        $user = $DB->get_record('user', ['id' => $submission->userid], '*', MUST_EXIST);
        $url = new \moodle_url('/mod/checkpoint/view.php', ['id' => $cm->id]);

        $message = new \core\message\message();
        $message->component = 'mod_checkpoint';
        $message->name = 'graded';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $user;
        $message->subject = get_string('notification:subject', 'mod_checkpoint', format_string($checkpoint->name));
        $message->fullmessage = get_string('notification:body', 'mod_checkpoint', (object)[
            'checkpoint' => format_string($checkpoint->name),
            'grade' => format_float($submission->grade, 2),
            'maxgrade' => format_float($checkpoint->grade, 2),
        ]);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = text_to_html($message->fullmessage, false, false, true);
        $message->smallmessage = get_string('notification:small', 'mod_checkpoint');
        $message->notification = 1;
        $message->contexturl = $url->out(false);
        $message->contexturlname = format_string($checkpoint->name);

        if (message_send($message)) {
            $DB->set_field('checkpoint_submission', 'notificationtime', time(), ['id' => $submissionid]);
        }
    }
}
