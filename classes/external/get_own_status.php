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

namespace mod_checkpoint\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Return the authenticated user's submission state.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_own_status extends external_api {
    /**
     * Define parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module id'),
        ]);
    }

    /**
     * Return the current user's status.
     *
     * @param int $cmid Course module id.
     * @return array
     */
    public static function execute(int $cmid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['cmid' => $cmid]);
        $cm = get_coursemodule_from_id('checkpoint', $params['cmid'], 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/checkpoint:submit', $context);

        $submission = $DB->get_record('checkpoint_submission', [
            'checkpointid' => $cm->instance,
            'userid' => $USER->id,
        ]);
        $result = [
            'hassubmission' => (bool)$submission,
            'status' => $submission?->status ?? 'none',
            'timemodified' => $submission?->timemodified ?? 0,
        ];
        if ($submission && $submission->grade !== null) {
            $result['grade'] = (float)$submission->grade;
        }
        return $result;
    }

    /**
     * Define the response structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'hassubmission' => new external_value(PARAM_BOOL, 'Whether a submission exists'),
            'status' => new external_value(PARAM_ALPHANUMEXT, 'Submission status'),
            'grade' => new external_value(PARAM_FLOAT, 'Published checkpoint grade', VALUE_OPTIONAL),
            'timemodified' => new external_value(PARAM_INT, 'Submission modified timestamp'),
        ]);
    }
}
