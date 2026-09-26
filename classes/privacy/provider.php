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

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for checkpoint submissions and evidence files.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe stored personal data.
     *
     * @param collection $items Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'checkpoint_submission',
            [
                'checkpointid' => 'privacy:metadata:submission:checkpointid',
                'userid' => 'privacy:metadata:submission:userid',
                'status' => 'privacy:metadata:submission:status',
                'submissiontext' => 'privacy:metadata:submission:text',
                'grade' => 'privacy:metadata:submission:grade',
                'feedback' => 'privacy:metadata:submission:feedback',
                'graderid' => 'privacy:metadata:submission:graderid',
                'timecreated' => 'privacy:metadata:submission:timecreated',
                'timemodified' => 'privacy:metadata:submission:timemodified',
                'timegraded' => 'privacy:metadata:submission:timegraded',
            ],
            'privacy:metadata:submission',
        );
        $items->add_subsystem_link('core_files', [], 'privacy:metadata:files');
        return $items;
    }

    /**
     * Return module contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {checkpoint_submission} s ON s.checkpointid = cm.instance
                 WHERE s.userid = :ownerid OR s.graderid = :graderid";
        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'checkpoint',
            'ownerid' => $userid,
            'graderid' => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Add users with data in a module context.
     *
     * @param userlist $userlist User list.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $sql = "SELECT s.userid
                  FROM {checkpoint_submission} s
                  JOIN {course_modules} cm ON cm.instance = s.checkpointid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, ['modname' => 'checkpoint', 'cmid' => $context->instanceid]);

        $sql = "SELECT s.graderid AS userid
                  FROM {checkpoint_submission} s
                  JOIN {course_modules} cm ON cm.instance = s.checkpointid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                 WHERE cm.id = :cmid AND s.graderid IS NOT NULL";
        $userlist->add_from_sql('userid', $sql, ['modname' => 'checkpoint', 'cmid' => $context->instanceid]);
    }

    /**
     * Export approved user data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (!$contextlist->count()) {
            return;
        }
        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('checkpoint', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $submission = $DB->get_record('checkpoint_submission', [
                'checkpointid' => $cm->instance,
                'userid' => $user->id,
            ]);
            if ($submission) {
                $data = (object)[
                    'status' => get_string('status:' . $submission->status, 'mod_checkpoint'),
                    'submissiontext' => $submission->submissiontext,
                    'grade' => $submission->grade,
                    'feedback' => $submission->feedback,
                    'timecreated' => transform::datetime($submission->timecreated),
                    'timemodified' => transform::datetime($submission->timemodified),
                    'timegraded' => $submission->timegraded ? transform::datetime($submission->timegraded) : null,
                ];
                $path = [get_string('privacy:path:submission', 'mod_checkpoint')];
                writer::with_context($context)
                    ->export_data($path, $data)
                    ->export_area_files($path, 'mod_checkpoint', 'evidence', $submission->id);
            }

            $graded = $DB->get_records('checkpoint_submission', [
                'checkpointid' => $cm->instance,
                'graderid' => $user->id,
            ], 'timegraded ASC', 'id, timegraded');
            if ($graded) {
                $gradingdata = [];
                foreach ($graded as $record) {
                    $gradingdata[] = (object)[
                        'submissionid' => $record->id,
                        'timegraded' => transform::datetime($record->timegraded),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('privacy:path:grading', 'mod_checkpoint')],
                    (object)['records' => $gradingdata],
                );
            }
        }
    }

    /**
     * Delete all submission data in a context.
     *
     * @param context $context Context to erase.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }
        $cm = get_coursemodule_from_id('checkpoint', $context->instanceid);
        if (!$cm) {
            return;
        }
        $checkpoint = $DB->get_record('checkpoint', ['id' => $cm->instance], '*', MUST_EXIST);
        $submissions = $DB->get_records('checkpoint_submission', ['checkpointid' => $cm->instance], '', 'id, userid');
        $affecteduserids = [];
        foreach ($submissions as $submission) {
            $affecteduserids[] = (int)$submission->userid;
            checkpoint_grade_item_update($checkpoint, ['userid' => $submission->userid, 'rawgrade' => null]);
        }
        get_file_storage()->delete_area_files($context->id, 'mod_checkpoint', 'evidence');
        $DB->delete_records('checkpoint_submission', ['checkpointid' => $cm->instance]);
        self::refresh_completion($cm, $affecteduserids);
        self::delete_summary_cache($cm->instance);
    }

    /**
     * Delete data for one user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        if (!$contextlist->count()) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            self::delete_user_in_context($context, [$userid]);
        }
    }

    /**
     * Delete data for an approved user list in one context.
     *
     * @param approved_userlist $userlist Approved users.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        self::delete_user_in_context($userlist->get_context(), $userlist->get_userids());
    }

    /**
     * Delete owned submissions and anonymise grader references.
     *
     * @param context $context Module context.
     * @param int[] $userids User ids.
     * @return void
     */
    private static function delete_user_in_context(context $context, array $userids): void {
        global $DB;

        if (!$context instanceof context_module || empty($userids)) {
            return;
        }
        $cm = get_coursemodule_from_id('checkpoint', $context->instanceid);
        if (!$cm) {
            return;
        }
        $checkpoint = $DB->get_record('checkpoint', ['id' => $cm->instance], '*', MUST_EXIST);
        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'privacyuser');
        $params = ['checkpointid' => $cm->instance] + $inparams;

        $submissions = $DB->get_records_select(
            'checkpoint_submission',
            "checkpointid = :checkpointid AND userid {$insql}",
            $params,
            '',
            'id, userid',
        );
        $affecteduserids = [];
        foreach ($submissions as $submission) {
            $affecteduserids[] = (int)$submission->userid;
            get_file_storage()->delete_area_files(
                $context->id,
                'mod_checkpoint',
                'evidence',
                $submission->id,
            );
            checkpoint_grade_item_update($checkpoint, ['userid' => $submission->userid, 'rawgrade' => null]);
        }
        $DB->delete_records_select(
            'checkpoint_submission',
            "checkpointid = :checkpointid AND userid {$insql}",
            $params,
        );
        $DB->set_field_select(
            'checkpoint_submission',
            'graderid',
            null,
            "checkpointid = :checkpointid AND graderid {$insql}",
            $params,
        );
        self::refresh_completion($cm, $affecteduserids);
        self::delete_summary_cache($cm->instance);
    }

    /**
     * Delete the cached status summary for one checkpoint.
     *
     * @param int $checkpointid Checkpoint id.
     * @return void
     */
    private static function delete_summary_cache(int $checkpointid): void {
        \cache::make('mod_checkpoint', 'summary')->delete('checkpoint_' . $checkpointid);
    }

    /**
     * Recalculate completion after privacy deletion changes the source domain data.
     *
     * @param \stdClass $cm Course module record.
     * @param int[] $userids Users whose owned submissions were deleted.
     * @return void
     */
    private static function refresh_completion(\stdClass $cm, array $userids): void {
        if (empty($userids)) {
            return;
        }

        $course = get_course($cm->course);
        $completion = new \completion_info($course);
        $cminfo = get_fast_modinfo($course)->get_cm($cm->id);
        if (!$completion->is_enabled($cminfo)) {
            return;
        }

        foreach (array_unique($userids) as $userid) {
            $completion->update_state($cminfo, COMPLETION_UNKNOWN, (int)$userid);
        }
    }
}
