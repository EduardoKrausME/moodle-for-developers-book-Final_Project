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

/**
 * Submission states used by the checkpoint domain.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission_status {
    /** Submission has not been formally sent. */
    public const DRAFT = 'draft';

    /** Submission is waiting for grading. */
    public const SUBMITTED = 'submitted';

    /** Submission has been graded. */
    public const GRADED = 'graded';

    /** Submission was returned to the learner for another attempt. */
    public const REOPENED = 'reopened';

    /**
     * Return all valid status values.
     *
     * @return string[]
     */
    public static function all(): array {
        return [self::DRAFT, self::SUBMITTED, self::GRADED, self::REOPENED];
    }
}
