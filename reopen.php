<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Reopen a submission using a CSRF-protected POST request.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$submissionid = required_param('submissionid', PARAM_INT);
$cm = get_coursemodule_from_id('checkpoint', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/checkpoint:grade', $context);
require_sesskey();
if (!data_submitted()) {
    throw new moodle_exception('invalidrequest');
}

$submission = $DB->get_record('checkpoint_submission', ['id' => $submissionid], '*', MUST_EXIST);
if ((int)$submission->checkpointid !== (int)$cm->instance) {
    throw new moodle_exception('error:invalidsubmission', 'mod_checkpoint');
}

\core\di::get(\mod_checkpoint\local\manager::class)->reopen($submissionid);
redirect(
    new moodle_url('/mod/checkpoint/submissions.php', ['id' => $cm->id]),
    get_string('submissionreopened', 'mod_checkpoint'),
    null,
    \core\output\notification::NOTIFY_SUCCESS,
);
