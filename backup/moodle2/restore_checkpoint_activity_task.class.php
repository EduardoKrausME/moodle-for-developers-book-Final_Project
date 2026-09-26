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
 * Restore task for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @category   backup
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/checkpoint/backup/moodle2/restore_checkpoint_stepslib.php');

class restore_checkpoint_activity_task extends restore_activity_task {
    /**
     * No plugin-specific restore settings are required.
     *
     * @return void
     */
    protected function define_my_settings(): void {
    }

    /**
     * Add the restore structure step.
     *
     * @return void
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_checkpoint_activity_structure_step('checkpoint_structure', 'checkpoint.xml'));
    }

    /**
     * Return content fields that need link decoding.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents(): array {
        return [new restore_decode_content('checkpoint', ['intro'], 'checkpoint')];
    }

    /**
     * Return content decoding rules.
     *
     * @return restore_decode_rule[]
     */
    public static function define_decode_rules(): array {
        return [
            new restore_decode_rule('CHECKPOINTVIEWBYID', '/mod/checkpoint/view.php?id=$1', 'course_module'),
            new restore_decode_rule('CHECKPOINTINDEX', '/mod/checkpoint/index.php?id=$1', 'course'),
        ];
    }
}
