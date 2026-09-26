<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint\event;

/**
 * Event fired when a checkpoint submission is graded.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class submission_graded extends \core\event\base {
    /**
     * Initialise event metadata.
     *
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'checkpoint_submission';
    }

    /**
     * Return event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventsubmissiongraded', 'mod_checkpoint');
    }

    /**
     * Return human-readable event description.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' graded submission '{$this->objectid}' "
            . "for user '{$this->relateduserid}' in checkpoint '{$this->other['checkpointid']}'.";
    }

    /**
     * Return the grading URL.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/checkpoint/grade.php', [
            'id' => $this->contextinstanceid,
            'submissionid' => $this->objectid,
        ]);
    }

    /**
     * Validate required other data.
     *
     * @return void
     */
    protected function validate_data(): void {
        parent::validate_data();
        if (!isset($this->other['checkpointid'], $this->other['graderid'])) {
            throw new \coding_exception('checkpointid and graderid must be set in other.');
        }
    }
}
