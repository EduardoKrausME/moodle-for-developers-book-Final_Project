<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Main activity page for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('checkpoint', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$checkpoint = $DB->get_record('checkpoint', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/checkpoint:view', $context);

$PAGE->set_url('/mod/checkpoint/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($checkpoint->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$completion = new completion_info($course);
if ($completion->is_enabled($cm) && has_capability('mod/checkpoint:submit', $context)) {
    $completion->set_module_viewed($cm);
}

$manager = \core\di::get(\mod_checkpoint\local\manager::class);

$submission = null;
if (has_capability('mod/checkpoint:submit', $context)) {
    $submission = $manager->get_submission($checkpoint->id, $USER->id);
}

$files = [];
if ($submission) {
    $storedfiles = get_file_storage()->get_area_files(
        $context->id,
        'mod_checkpoint',
        'evidence',
        $submission->id,
        'filename',
        false,
    );
    foreach ($storedfiles as $file) {
        $files[] = [
            'name' => $file->get_filename(),
            'url' => moodle_url::make_pluginfile_url(
                $context->id,
                'mod_checkpoint',
                'evidence',
                $submission->id,
                $file->get_filepath(),
                $file->get_filename(),
                true,
            )->out(false),
        ];
    }
}

$canedit = has_capability('mod/checkpoint:submit', $context)
    && (!$submission || $submission->status !== \mod_checkpoint\local\submission_status::GRADED);

$statusoutput = null;
if (has_capability('mod/checkpoint:submit', $context)) {
    $statusoutput = new \mod_checkpoint\output\student_status(
        checkpoint: $checkpoint,
        submission: $submission,
        canedit: $canedit,
        editurl: (new moodle_url('/mod/checkpoint/submission.php', ['id' => $cm->id]))->out(false),
        late: $submission ? $manager->is_late($checkpoint, $submission) : false,
        files: $files,
    );
}

$summary = null;
if (has_capability('mod/checkpoint:grade', $context)) {
    $summary = $manager->get_summary($checkpoint->id);
    $PAGE->requires->js_call_amd('mod_checkpoint/dashboard', 'init', [$cm->id]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($checkpoint->name));

if (trim((string)$checkpoint->intro) !== '') {
    echo $OUTPUT->box(format_module_intro('checkpoint', $checkpoint, $cm->id), 'generalbox mod_introbox');
}

if (!empty($checkpoint->duedate)) {
    echo $OUTPUT->notification(
        get_string('duedatevalue', 'mod_checkpoint', userdate($checkpoint->duedate)),
        \core\output\notification::NOTIFY_INFO,
    );
}

if ($statusoutput) {
    echo $OUTPUT->render($statusoutput);
}

if ($summary) {
    $cards = [
        'submitted' => get_string('status:submitted', 'mod_checkpoint'),
        'graded' => get_string('status:graded', 'mod_checkpoint'),
        'reopened' => get_string('status:reopened', 'mod_checkpoint'),
        'late' => get_string('late', 'mod_checkpoint'),
    ];

    echo html_writer::start_div('mod-checkpoint-dashboard row g-3 mb-3', ['data-checkpoint-dashboard' => '1']);
    foreach ($cards as $key => $label) {
        echo html_writer::start_div('col-6 col-lg-3');
        echo html_writer::start_div('card h-100');
        echo html_writer::start_div('card-body');
        echo html_writer::div($label, 'text-muted small');
        echo html_writer::div(
            (string)$summary[$key],
            'fs-3 fw-semibold',
            ['data-checkpoint-' . $key => '1'],
        );
        echo html_writer::end_div();
        echo html_writer::end_div();
        echo html_writer::end_div();
    }
    echo html_writer::end_div();

    echo $OUTPUT->single_button(
        new moodle_url('/mod/checkpoint/submissions.php', ['id' => $cm->id]),
        get_string('viewsubmissions', 'mod_checkpoint'),
        'get',
        ['class' => 'mb-3'],
    );
}

echo $OUTPUT->footer();
