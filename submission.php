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
 * Learner submission endpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('checkpoint', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$checkpoint = $DB->get_record('checkpoint', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/checkpoint:submit', $context);

$PAGE->set_url('/mod/checkpoint/submission.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('editsubmission', 'mod_checkpoint'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$manager = \core\di::get(\mod_checkpoint\local\manager::class);
$submission = $manager->get_submission($checkpoint->id, $USER->id);
if ($submission && $submission->status === \mod_checkpoint\local\submission_status::GRADED) {
    throw new moodle_exception('error:gradedlocked', 'mod_checkpoint');
}

$fileoptions = \mod_checkpoint\local\manager::get_file_options($course->id);
$form = new \mod_checkpoint\form\submission_form(null, [
    'checkpoint' => $checkpoint,
    'context' => $context,
    'fileoptions' => $fileoptions,
]);

$formdata = new stdClass();
$formdata->submissiontext = [
    'text' => $submission?->submissiontext ?? '',
    'format' => $submission?->submissionformat ?? FORMAT_HTML,
];
if ($checkpoint->allowfile) {
    file_prepare_standard_filemanager(
        $formdata,
        'evidence',
        $fileoptions,
        $context,
        'mod_checkpoint',
        'evidence',
        $submission?->id ?? 0,
    );
}
$form->set_data($formdata);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/checkpoint/view.php', ['id' => $cm->id]));
}
if ($data = $form->get_data()) {
    $manager->submit($checkpoint->id, $USER->id, (array)$data);
    redirect(
        new moodle_url('/mod/checkpoint/view.php', ['id' => $cm->id]),
        get_string('submissionsaved', 'mod_checkpoint'),
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editsubmission', 'mod_checkpoint'));
$form->display();
echo $OUTPUT->footer();
