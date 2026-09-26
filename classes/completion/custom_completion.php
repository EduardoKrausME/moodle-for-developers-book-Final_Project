<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint\completion;

use core_completion\activity_custom_completion;
use mod_checkpoint\local\submission_status;

/**
 * Custom completion rules for checkpoint submissions.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class custom_completion extends activity_custom_completion {
    /**
     * Fetch the completion state for a custom rule.
     *
     * @param string $rule Rule name.
     * @return int
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);
        $submission = $DB->get_record('checkpoint_submission', [
            'checkpointid' => $this->cm->instance,
            'userid' => $this->userid,
        ]);

        if (!$submission) {
            return COMPLETION_INCOMPLETE;
        }

        $complete = match ($rule) {
            'completionsubmit' => in_array(
                $submission->status,
                [submission_status::SUBMITTED, submission_status::GRADED],
                true,
            ),
            'completiongrade' => $submission->status === submission_status::GRADED,
        };

        return $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Return custom rules defined by the module.
     *
     * @return string[]
     */
    public static function get_defined_custom_rules(): array {
        return ['completionsubmit', 'completiongrade'];
    }

    /**
     * Return descriptions displayed in activity completion settings.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [
            'completionsubmit' => get_string('completiondetail:submit', 'mod_checkpoint'),
            'completiongrade' => get_string('completiondetail:grade', 'mod_checkpoint'),
        ];
    }

    /**
     * Return the preferred order for all completion rules.
     *
     * @return string[]
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionsubmit',
            'completiongrade',
            'completionusegrade',
            'completionpassgrade',
        ];
    }
}
