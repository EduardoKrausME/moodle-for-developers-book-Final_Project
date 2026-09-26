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

/**
 * Teacher grading endpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);
$submissionid = required_param('submissionid', PARAM_INT);

$cm = get_coursemodule_from_id('checkpoint', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$checkpoint = $DB->get_record('checkpoint', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/checkpoint:grade', $context);
$submission = $DB->get_record('checkpoint_submission', ['id' => $submissionid], '*', MUST_EXIST);
if ((int)$submission->checkpointid !== (int)$checkpoint->id) {
    throw new moodle_exception('error:invalidsubmission', 'mod_checkpoint');
}

$PAGE->set_url('/mod/checkpoint/grade.php', ['id' => $cm->id, 'submissionid' => $submissionid]);
$PAGE->set_title(get_string('gradeverb', 'mod_checkpoint'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$form = new \mod_checkpoint\form\grade_form(null, [
    'checkpoint' => $checkpoint,
    'context' => $context,
]);
$form->set_data((object)[
    'submissionid' => $submission->id,
    'grade' => $submission->grade,
    'feedback' => [
        'text' => $submission->feedback,
        'format' => $submission->feedbackformat,
    ],
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/checkpoint/submissions.php', ['id' => $cm->id]));
}
if ($data = $form->get_data()) {
    \core\di::get(\mod_checkpoint\local\manager::class)->grade(
        $submission->id,
        $USER->id,
        (float)$data->grade,
        (string)$data->feedback['text'],
        (int)$data->feedback['format'],
    );
    redirect(
        new moodle_url('/mod/checkpoint/submissions.php', ['id' => $cm->id]),
        get_string('gradesaved', 'mod_checkpoint'),
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

$user = $DB->get_record('user', ['id' => $submission->userid], '*', MUST_EXIST);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('gradefor', 'mod_checkpoint', fullname($user)));

if ($submission->submissiontext !== '') {
    echo $OUTPUT->box(format_text(
        $submission->submissiontext,
        $submission->submissionformat,
        ['context' => $context],
    ));
}

$files = get_file_storage()->get_area_files(
    $context->id,
    'mod_checkpoint',
    'evidence',
    $submission->id,
    'filename',
    false,
);
if ($files) {
    echo $OUTPUT->heading(get_string('evidence', 'mod_checkpoint'), 4);
    echo html_writer::start_tag('ul');
    foreach ($files as $file) {
        $url = moodle_url::make_pluginfile_url(
            $context->id,
            'mod_checkpoint',
            'evidence',
            $submission->id,
            $file->get_filepath(),
            $file->get_filename(),
            true,
        );
        echo html_writer::tag('li', html_writer::link($url, s($file->get_filename())));
    }
    echo html_writer::end_tag('ul');
}

$form->display();
echo $OUTPUT->footer();
