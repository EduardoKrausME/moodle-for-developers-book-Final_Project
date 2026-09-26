<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Paginated teacher submission queue.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$status = optional_param('status', '', PARAM_ALPHANUMEXT);
$perpage = 30;

$cm = get_coursemodule_from_id('checkpoint', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$checkpoint = $DB->get_record('checkpoint', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/checkpoint:grade', $context);

if ($status !== '' && !in_array($status, \mod_checkpoint\local\submission_status::all(), true)) {
    throw new moodle_exception('invalidparameter');
}

$baseurl = new moodle_url('/mod/checkpoint/submissions.php', ['id' => $cm->id]);
if ($status !== '') {
    $baseurl->param('status', $status);
}
$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('submissions', 'mod_checkpoint'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$params = ['checkpointid' => $checkpoint->id];
$where = 's.checkpointid = :checkpointid';
if ($status !== '') {
    $where .= ' AND s.status = :status';
    $params['status'] = $status;
}

$total = $DB->count_records_select(
    'checkpoint_submission',
    'checkpointid = :checkpointid' . ($status !== '' ? ' AND status = :status' : ''),
    $params,
);

$sql = "SELECT s.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
               u.middlename, u.alternatename
          FROM {checkpoint_submission} s
          JOIN {user} u ON u.id = s.userid
         WHERE {$where}
      ORDER BY s.timemodified DESC, s.id DESC";
$records = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

$PAGE->requires->js_call_amd('mod_checkpoint/dashboard', 'init', [$cm->id]);
$summary = \core\di::get(\mod_checkpoint\local\manager::class)->get_summary($checkpoint->id);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('submissions', 'mod_checkpoint'));

$filteroptions = ['' => get_string('all')];
foreach (\mod_checkpoint\local\submission_status::all() as $state) {
    $filteroptions[$state] = get_string('status:' . $state, 'mod_checkpoint');
}
$select = new single_select(
    new moodle_url('/mod/checkpoint/submissions.php', ['id' => $cm->id]),
    'status',
    $filteroptions,
    $status,
    false,
);
$select->set_label(get_string('filterbystatus', 'mod_checkpoint'));
echo $OUTPUT->render($select);

$table = new html_table();
$table->head = [
    get_string('student', 'mod_checkpoint'),
    get_string('status', 'mod_checkpoint'),
    get_string('timemodified'),
    get_string('late', 'mod_checkpoint'),
    get_string('grade', 'mod_checkpoint'),
    get_string('actions'),
];
$table->attributes['class'] = 'generaltable mt-3';

foreach ($records as $record) {
    $user = (object)[
        'id' => $record->userid,
        'firstname' => $record->firstname,
        'lastname' => $record->lastname,
        'firstnamephonetic' => $record->firstnamephonetic,
        'lastnamephonetic' => $record->lastnamephonetic,
        'middlename' => $record->middlename,
        'alternatename' => $record->alternatename,
    ];
    $actions = [];
    $actions[] = html_writer::link(
        new moodle_url('/mod/checkpoint/grade.php', ['id' => $cm->id, 'submissionid' => $record->id]),
        get_string('gradeverb', 'mod_checkpoint'),
        ['class' => 'btn btn-sm btn-primary'],
    );
    if ($record->status === \mod_checkpoint\local\submission_status::GRADED) {
        $actions[] = $OUTPUT->single_button(
            new moodle_url('/mod/checkpoint/reopen.php', ['id' => $cm->id, 'submissionid' => $record->id]),
            get_string('reopen', 'mod_checkpoint'),
            'post',
            ['class' => 'd-inline-block'],
        );
    }

    $table->data[] = [
        fullname($user),
        get_string('status:' . $record->status, 'mod_checkpoint'),
        userdate($record->timemodified),
        (!empty($checkpoint->duedate) && $record->timemodified > $checkpoint->duedate) ? get_string('yes') : get_string('no'),
        $record->grade === null ? '-' : format_float($record->grade, 2) . ' / ' . format_float($checkpoint->grade, 2),
        implode(' ', $actions),
    ];
}

echo html_writer::div(
    get_string('summarycounts', 'mod_checkpoint', (object)[
        'submitted' => $summary['submitted'],
        'graded' => $summary['graded'],
        'reopened' => $summary['reopened'],
        'late' => $summary['late'],
    ]),
    'text-muted mb-2',
);
echo html_writer::table($table);
echo $OUTPUT->paging_bar($total, $page, $perpage, $baseurl);

echo $OUTPUT->footer();
