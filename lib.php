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
 * Core callbacks for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Declare supported Moodle features.
 *
 * @param string $feature Feature constant.
 * @return bool|string|null
 */
function checkpoint_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_GRADE_HAS_GRADE => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_COMPLETION_HAS_RULES => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_ASSESSMENT,
        default => null,
    };
}

/**
 * Create an activity instance.
 *
 * @param stdClass $data Module form data.
 * @param moodleform_mod|null $mform Module form.
 * @return int
 */
function checkpoint_add_instance(stdClass $data, ?moodleform_mod $mform = null): int {
    return \core\di::get(\mod_checkpoint\local\manager::class)->create_instance($data);
}

/**
 * Update an activity instance.
 *
 * @param stdClass $data Module form data.
 * @param moodleform_mod|null $mform Module form.
 * @return bool
 */
function checkpoint_update_instance(stdClass $data, ?moodleform_mod $mform = null): bool {
    return \core\di::get(\mod_checkpoint\local\manager::class)->update_instance($data);
}

/**
 * Delete an activity instance.
 *
 * @param int $id Checkpoint id.
 * @return bool
 */
function checkpoint_delete_instance(int $id): bool {
    return \core\di::get(\mod_checkpoint\local\manager::class)->delete_instance($id);
}

/**
 * Serve protected evidence files.
 *
 * @param stdClass $course Course record.
 * @param stdClass $cm Course module record.
 * @param context $context File context.
 * @param string $filearea File area.
 * @param array $args Remaining file path arguments.
 * @param bool $forcedownload Force download flag.
 * @param array $options File serving options.
 * @return bool
 */
function checkpoint_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    global $DB, $USER;

    require_login($course, true, $cm);
    if ($context->contextlevel !== CONTEXT_MODULE || $filearea !== 'evidence' || empty($args)) {
        return false;
    }

    $itemid = (int)array_shift($args);
    $submission = $DB->get_record('checkpoint_submission', ['id' => $itemid]);
    if (!$submission || (int)$submission->checkpointid !== (int)$cm->instance) {
        return false;
    }
    if ((int)$submission->userid !== (int)$USER->id && !has_capability('mod/checkpoint:grade', $context)) {
        return false;
    }

    $filename = array_pop($args);
    $filepath = '/' . (empty($args) ? '' : implode('/', $args) . '/');
    $file = get_file_storage()->get_file(
        $context->id,
        'mod_checkpoint',
        'evidence',
        $itemid,
        $filepath,
        $filename,
    );
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);
    return true;
}

/**
 * Create or update the grade item and optionally publish one or more grades.
 *
 * @param stdClass $checkpoint Checkpoint record.
 * @param array|stdClass|null $grades Grade or grades to publish.
 * @return int
 */
function checkpoint_grade_item_update(stdClass $checkpoint, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $checkpoint->name,
        'gradetype' => GRADE_TYPE_VALUE,
        'grademin' => 0,
        'grademax' => (float)$checkpoint->grade,
    ];

    return grade_update(
        'mod/checkpoint',
        $checkpoint->course,
        'mod',
        'checkpoint',
        $checkpoint->id,
        0,
        $grades,
        $params,
    );
}

/**
 * Remove the grade item for an activity.
 *
 * @param stdClass $checkpoint Checkpoint record.
 * @return int
 */
function checkpoint_grade_item_delete(stdClass $checkpoint): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        'mod/checkpoint',
        $checkpoint->course,
        'mod',
        'checkpoint',
        $checkpoint->id,
        0,
        null,
        ['deleted' => 1],
    );
}

/**
 * Rebuild gradebook data from checkpoint submissions.
 *
 * @param stdClass $checkpoint Checkpoint record.
 * @param int $userid Optional user id.
 * @param bool $nullifnone Whether to send a null grade if no grade exists.
 * @return void
 */
function checkpoint_update_grades(stdClass $checkpoint, int $userid = 0, bool $nullifnone = true): void {
    global $DB;

    $params = ['checkpointid' => $checkpoint->id, 'status' => \mod_checkpoint\local\submission_status::GRADED];
    $select = 'checkpointid = :checkpointid AND status = :status';
    if ($userid) {
        $select .= ' AND userid = :userid';
        $params['userid'] = $userid;
    }

    $records = $DB->get_records_select('checkpoint_submission', $select, $params, '', 'id, userid, grade');
    if ($records) {
        $grades = [];
        foreach ($records as $record) {
            $grades[$record->userid] = (object)[
                'userid' => $record->userid,
                'rawgrade' => $record->grade,
            ];
        }
        checkpoint_grade_item_update($checkpoint, $grades);
    } else if ($userid && $nullifnone) {
        checkpoint_grade_item_update($checkpoint, ['userid' => $userid, 'rawgrade' => null]);
    } else {
        checkpoint_grade_item_update($checkpoint);
    }
}

/**
 * Provide course module information used by course caches and completion.
 *
 * @param stdClass $coursemodule Course module record.
 * @return cached_cm_info|false
 */
function checkpoint_get_coursemodule_info(stdClass $coursemodule) {
    global $DB;

    $checkpoint = $DB->get_record(
        'checkpoint',
        ['id' => $coursemodule->instance],
        'id, name, intro, introformat, completionsubmit, completiongrade',
    );
    if (!$checkpoint) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $checkpoint->name;
    if ($coursemodule->showdescription) {
        $result->content = format_module_intro('checkpoint', $checkpoint, $coursemodule->id, false);
    }
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionsubmit'] = $checkpoint->completionsubmit;
        $result->customdata['customcompletionrules']['completiongrade'] = $checkpoint->completiongrade;
    }
    return $result;
}
