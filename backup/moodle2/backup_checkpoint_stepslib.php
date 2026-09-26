<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Backup structure for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @category   backup
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_checkpoint_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the backup tree and its data sources.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $checkpoint = new backup_nested_element('checkpoint', ['id'], [
            'name', 'intro', 'introformat', 'duedate', 'grade', 'allowtext', 'allowfile',
            'completionsubmit', 'completiongrade', 'timecreated', 'timemodified',
        ]);
        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], [
            'userid', 'status', 'submissiontext', 'submissionformat', 'grade', 'feedback',
            'feedbackformat', 'graderid', 'timecreated', 'timemodified', 'timegraded', 'notificationtime',
        ]);

        $checkpoint->add_child($submissions);
        $submissions->add_child($submission);

        $checkpoint->set_source_table('checkpoint', ['id' => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $submission->set_source_table('checkpoint_submission', ['checkpointid' => backup::VAR_PARENTID]);
        }

        $submission->annotate_ids('user', 'userid');
        $submission->annotate_ids('user', 'graderid');
        $checkpoint->annotate_files('mod_checkpoint', 'intro', null);
        $submission->annotate_files('mod_checkpoint', 'evidence', 'id');

        return $this->prepare_activity_structure($checkpoint);
    }
}
