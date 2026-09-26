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

namespace mod_checkpoint\local;

use cache;
use context_module;
use moodle_exception;

/**
 * Application service for checkpoint state transitions.
 *
 * Database and clock dependencies are explicit so time-sensitive domain rules
 * can be tested without relying on global state or the wall clock.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Moodle database connection.
     *
     * @var \moodle_database
     */
    private readonly \moodle_database $db;

    /**
     * Moodle clock service.
     *
     * @var \core\clock
     */
    private readonly \core\clock $clock;

    /**
     * Create the service.
     *
     * @param \moodle_database $db Moodle database connection.
     * @param \core\clock $clock Moodle clock service.
     */
    public function __construct(\moodle_database $db, \core\clock $clock) {
        $this->db = $db;
        $this->clock = $clock;
    }

    /**
     * Create a checkpoint instance.
     *
     * @param \stdClass $data Validated module form data.
     * @return int New checkpoint id.
     */
    public function create_instance(\stdClass $data): int {
        $now = $this->clock->time();
        $data->timecreated = $now;
        $data->timemodified = $now;
        $data->duedate = (int)($data->duedate ?? 0);
        $data->allowtext = empty($data->allowtext) ? 0 : 1;
        $data->allowfile = empty($data->allowfile) ? 0 : 1;
        $data->completionsubmit = empty($data->completionsubmit) ? 0 : 1;
        $data->completiongrade = empty($data->completiongrade) ? 0 : 1;

        $id = $this->db->insert_record('checkpoint', $data);
        $checkpoint = $this->db->get_record('checkpoint', ['id' => $id], '*', MUST_EXIST);
        checkpoint_grade_item_update($checkpoint);
        return $id;
    }

    /**
     * Update a checkpoint instance.
     *
     * @param \stdClass $data Validated module form data.
     * @return bool
     */
    public function update_instance(\stdClass $data): bool {
        $data->id = $data->instance;
        $data->timemodified = $this->clock->time();
        $data->duedate = (int)($data->duedate ?? 0);
        $data->allowtext = empty($data->allowtext) ? 0 : 1;
        $data->allowfile = empty($data->allowfile) ? 0 : 1;
        $data->completionsubmit = empty($data->completionsubmit) ? 0 : 1;
        $data->completiongrade = empty($data->completiongrade) ? 0 : 1;

        $updated = $this->db->update_record('checkpoint', $data);
        $checkpoint = $this->db->get_record('checkpoint', ['id' => $data->id], '*', MUST_EXIST);
        checkpoint_grade_item_update($checkpoint);
        $this->invalidate_summary($checkpoint->id);
        return $updated;
    }

    /**
     * Delete a checkpoint instance and all data owned by it.
     *
     * @param int $checkpointid Checkpoint id.
     * @return bool
     */
    public function delete_instance(int $checkpointid): bool {
        $checkpoint = $this->db->get_record('checkpoint', ['id' => $checkpointid]);
        if (!$checkpoint) {
            return false;
        }

        $cm = get_coursemodule_from_instance('checkpoint', $checkpointid, $checkpoint->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        get_file_storage()->delete_area_files($context->id, 'mod_checkpoint');

        checkpoint_grade_item_delete($checkpoint);
        $this->db->delete_records('checkpoint_submission', ['checkpointid' => $checkpointid]);
        $this->db->delete_records('checkpoint', ['id' => $checkpointid]);
        $this->invalidate_summary($checkpointid);
        return true;
    }

    /**
     * Create or replace the learner's current submission.
     *
     * @param int $checkpointid Checkpoint id.
     * @param int $userid Submission owner.
     * @param array $data Submission text, format and optional draft file id.
     * @return int Submission id.
     */
    public function submit(int $checkpointid, int $userid, array $data): int {
        global $USER;

        $checkpoint = $this->db->get_record('checkpoint', ['id' => $checkpointid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('checkpoint', $checkpointid, $checkpoint->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);

        require_capability('mod/checkpoint:submit', $context);
        if ($userid !== (int)$USER->id && !has_capability('mod/checkpoint:manage', $context)) {
            throw new moodle_exception('error:cannotothersubmit', 'mod_checkpoint');
        }

        $submission = $this->db->get_record('checkpoint_submission', [
            'checkpointid' => $checkpointid,
            'userid' => $userid,
        ]);
        $now = $this->clock->time();
        $isnew = !$submission;

        if (!$submission) {
            $submission = (object)[
                'checkpointid' => $checkpointid,
                'userid' => $userid,
                'status' => submission_status::SUBMITTED,
                'submissiontext' => '',
                'submissionformat' => FORMAT_HTML,
                'grade' => null,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
                'graderid' => null,
                'timecreated' => $now,
                'timemodified' => $now,
                'timegraded' => 0,
                'notificationtime' => 0,
            ];
        }

        if (!$isnew && $submission->status === submission_status::GRADED) {
            throw new moodle_exception('error:gradedlocked', 'mod_checkpoint');
        }

        $textdata = $data['submissiontext'] ?? ['text' => '', 'format' => FORMAT_HTML];
        $submission->submissiontext = $checkpoint->allowtext ? trim((string)($textdata['text'] ?? '')) : '';
        $submission->submissionformat = (int)($textdata['format'] ?? FORMAT_HTML);
        $submission->status = submission_status::SUBMITTED;
        $submission->timemodified = $now;
        $submission->grade = null;
        $submission->feedback = '';
        $submission->graderid = null;
        $submission->timegraded = 0;
        $submission->notificationtime = 0;

        $transaction = $this->db->start_delegated_transaction();
        if ($isnew) {
            $submission->id = $this->db->insert_record('checkpoint_submission', $submission);
        } else {
            $this->db->update_record('checkpoint_submission', $submission);
        }

        if ($checkpoint->allowfile && array_key_exists('evidence_filemanager', $data)) {
            $filedata = (object)['evidence_filemanager' => (int)$data['evidence_filemanager']];
            file_postupdate_standard_filemanager(
                $filedata,
                'evidence',
                self::get_file_options($checkpoint->course),
                $context,
                'mod_checkpoint',
                'evidence',
                $submission->id,
            );
        } else if (!$checkpoint->allowfile) {
            get_file_storage()->delete_area_files($context->id, 'mod_checkpoint', 'evidence', $submission->id);
        }

        $hasfiles = get_file_storage()->get_area_files(
            $context->id,
            'mod_checkpoint',
            'evidence',
            $submission->id,
            'id',
            false,
        );
        if ($submission->submissiontext === '' && empty($hasfiles)) {
            throw new moodle_exception('error:emptysubmission', 'mod_checkpoint');
        }

        $transaction->allow_commit();
        $this->invalidate_summary($checkpointid);
        $this->refresh_completion($cm, $userid);

        $event = \mod_checkpoint\event\submission_created::create([
            'context' => $context,
            'objectid' => $submission->id,
            'relateduserid' => $userid,
            'other' => ['checkpointid' => $checkpointid, 'updated' => !$isnew],
        ]);
        $event->trigger();

        return (int)$submission->id;
    }

    /**
     * Grade a submission and publish the resulting grade.
     *
     * @param int $submissionid Submission id.
     * @param int $graderid Grader user id.
     * @param float $grade Grade value.
     * @param string $feedback Feedback text.
     * @param int $feedbackformat Moodle text format.
     * @return void
     */
    public function grade(
        int $submissionid,
        int $graderid,
        float $grade,
        string $feedback,
        int $feedbackformat = 1,
    ): void {
        global $USER;

        $submission = $this->db->get_record('checkpoint_submission', ['id' => $submissionid], '*', MUST_EXIST);
        $checkpoint = $this->db->get_record('checkpoint', ['id' => $submission->checkpointid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('checkpoint', $checkpoint->id, $checkpoint->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/checkpoint:grade', $context);

        if ($graderid !== (int)$USER->id && !has_capability('mod/checkpoint:manage', $context)) {
            throw new moodle_exception('error:invalidgrader', 'mod_checkpoint');
        }
        if ($grade < 0 || $grade > (float)$checkpoint->grade) {
            throw new moodle_exception('error:graderange', 'mod_checkpoint', '', $checkpoint->grade);
        }

        $now = $this->clock->time();
        $submission->status = submission_status::GRADED;
        $submission->grade = $grade;
        $submission->feedback = $feedback;
        $submission->feedbackformat = $feedbackformat;
        $submission->graderid = $graderid;
        $submission->timegraded = $now;
        $submission->notificationtime = 0;
        $this->db->update_record('checkpoint_submission', $submission);

        checkpoint_grade_item_update($checkpoint, [
            'userid' => $submission->userid,
            'rawgrade' => $grade,
        ]);
        $this->invalidate_summary($checkpoint->id);
        $this->refresh_completion($cm, $submission->userid);

        $event = \mod_checkpoint\event\submission_graded::create([
            'context' => $context,
            'objectid' => $submission->id,
            'relateduserid' => $submission->userid,
            'other' => ['checkpointid' => $checkpoint->id, 'graderid' => $graderid],
        ]);
        $event->trigger();

        if (get_config('mod_checkpoint', 'notifygrade')) {
            $task = new \mod_checkpoint\task\send_grade_notification();
            $task->set_custom_data(['submissionid' => $submission->id]);
            \core\task\manager::queue_adhoc_task($task);
        }
    }

    /**
     * Reopen a graded or submitted attempt for learner editing.
     *
     * @param int $submissionid Submission id.
     * @return void
     */
    public function reopen(int $submissionid): void {
        $submission = $this->db->get_record('checkpoint_submission', ['id' => $submissionid], '*', MUST_EXIST);
        $checkpoint = $this->db->get_record('checkpoint', ['id' => $submission->checkpointid], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('checkpoint', $checkpoint->id, $checkpoint->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/checkpoint:grade', $context);

        $submission->status = submission_status::REOPENED;
        $submission->grade = null;
        $submission->feedback = '';
        $submission->graderid = null;
        $submission->timegraded = 0;
        $submission->notificationtime = 0;
        $this->db->update_record('checkpoint_submission', $submission);

        checkpoint_grade_item_update($checkpoint, [
            'userid' => $submission->userid,
            'rawgrade' => null,
        ]);
        $this->invalidate_summary($checkpoint->id);
        $this->refresh_completion($cm, $submission->userid);
    }

    /**
     * Get a learner submission if it exists.
     *
     * @param int $checkpointid Checkpoint id.
     * @param int $userid User id.
     * @return \stdClass|null
     */
    public function get_submission(int $checkpointid, int $userid): ?\stdClass {
        $record = $this->db->get_record('checkpoint_submission', [
            'checkpointid' => $checkpointid,
            'userid' => $userid,
        ]);
        return $record ?: null;
    }

    /**
     * Return cached status counters for a checkpoint.
     *
     * @param int $checkpointid Checkpoint id.
     * @return array<string, int>
     */
    public function get_summary(int $checkpointid): array {
        $cache = cache::make('mod_checkpoint', 'summary');
        $key = 'checkpoint_' . $checkpointid;
        $cached = $cache->get($key);
        if ($cached !== false) {
            return $cached;
        }

        $summary = [
            'submitted' => 0,
            'graded' => 0,
            'reopened' => 0,
            'draft' => 0,
            'total' => 0,
            'late' => 0,
        ];
        $records = $this->db->get_records_sql(
            'SELECT status, COUNT(1) AS total
               FROM {checkpoint_submission}
              WHERE checkpointid = :checkpointid
           GROUP BY status',
            ['checkpointid' => $checkpointid],
        );
        foreach ($records as $record) {
            if (array_key_exists($record->status, $summary)) {
                $summary[$record->status] = (int)$record->total;
            }
            $summary['total'] += (int)$record->total;
        }

        $checkpoint = $this->db->get_record('checkpoint', ['id' => $checkpointid], 'id, duedate', MUST_EXIST);
        if (!empty($checkpoint->duedate)) {
            $summary['late'] = $this->db->count_records_select(
                'checkpoint_submission',
                'checkpointid = :checkpointid AND timemodified > :duedate',
                ['checkpointid' => $checkpointid, 'duedate' => $checkpoint->duedate],
            );
        }

        $cache->set($key, $summary);
        return $summary;
    }

    /**
     * Return file manager options shared by UI and persistence.
     *
     * @param int $courseid Course id used to resolve upload limits.
     * @return array
     */
    public static function get_file_options(int $courseid): array {
        global $CFG;

        $course = get_course($courseid);
        $configured = (int)get_config('mod_checkpoint', 'maxbytes');
        $maxbytes = get_max_upload_file_size($CFG->maxbytes, $course->maxbytes, $configured ?: 0);
        return [
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => 1,
            'accepted_types' => '*',
            'return_types' => \FILE_INTERNAL,
        ];
    }

    /**
     * Determine whether a submission was last changed after the due date.
     *
     * @param \stdClass $checkpoint Checkpoint record.
     * @param \stdClass $submission Submission record.
     * @return bool
     */
    public function is_late(\stdClass $checkpoint, \stdClass $submission): bool {
        return !empty($checkpoint->duedate) && $submission->timemodified > $checkpoint->duedate;
    }

    /**
     * Purge one cached summary.
     *
     * @param int $checkpointid Checkpoint id.
     * @return void
     */
    public function invalidate_summary(int $checkpointid): void {
        cache::make('mod_checkpoint', 'summary')->delete('checkpoint_' . $checkpointid);
    }

    /**
     * Recalculate completion after a domain state transition.
     *
     * @param \cm_info|\stdClass $cm Course module.
     * @param int $userid User id.
     * @return void
     */
    private function refresh_completion($cm, int $userid): void {
        $course = get_course($cm->course);
        $completion = new \completion_info($course);
        $cminfo = get_fast_modinfo($course)->get_cm($cm->id);
        if ($completion->is_enabled($cminfo)) {
            $completion->update_state($cminfo, COMPLETION_UNKNOWN, $userid);
        }
    }
}
