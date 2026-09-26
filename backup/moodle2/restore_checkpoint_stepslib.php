<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Restore structure for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @category   backup
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_checkpoint_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define restore paths.
     *
     * @return restore_path_element[]
     */
    protected function define_structure(): array {
        $paths = [new restore_path_element('checkpoint', '/activity/checkpoint')];
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('checkpoint_submission', '/activity/checkpoint/submissions/submission');
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore the activity instance.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_checkpoint($data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->duedate = $this->apply_date_offset($data->duedate);
        $newid = $DB->insert_record('checkpoint', $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Restore one submission and record its new mapping for files.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_checkpoint_submission($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->checkpointid = $this->get_new_parentid('checkpoint');
        $data->userid = $this->get_mappingid('user', $data->userid, 0);
        $data->graderid = empty($data->graderid) ? null : $this->get_mappingid('user', $data->graderid, null);
        if (!$data->userid) {
            return;
        }

        $newid = $DB->insert_record('checkpoint_submission', $data);
        $this->set_mapping('checkpoint_submission', $oldid, $newid, true);
    }

    /**
     * Restore related files after mappings are available.
     *
     * @return void
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_checkpoint', 'intro', null);
        $this->add_related_files('mod_checkpoint', 'evidence', 'checkpoint_submission');
    }
}
