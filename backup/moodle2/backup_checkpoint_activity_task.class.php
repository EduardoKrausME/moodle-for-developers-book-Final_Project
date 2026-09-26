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
 * Backup task for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @category   backup
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/checkpoint/backup/moodle2/backup_checkpoint_stepslib.php');

class backup_checkpoint_activity_task extends backup_activity_task {
    /**
     * No plugin-specific backup settings are required.
     *
     * @return void
     */
    protected function define_my_settings(): void {
    }

    /**
     * Add the activity structure step.
     *
     * @return void
     */
    protected function define_my_steps(): void {
        $this->add_step(new backup_checkpoint_activity_structure_step('checkpoint_structure', 'checkpoint.xml'));
    }

    /**
     * Encode links pointing to this activity.
     *
     * @param string $content Content containing links.
     * @return string
     */
    public static function encode_content_links($content): string {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');
        $content = preg_replace(
            "/({$base}\\/mod\\/checkpoint\\/index.php\\?id=)([0-9]+)/",
            '$@CHECKPOINTINDEX*$2@$',
            $content,
        );
        return preg_replace(
            "/({$base}\\/mod\\/checkpoint\\/view.php\\?id=)([0-9]+)/",
            '$@CHECKPOINTVIEWBYID*$2@$',
            $content,
        );
    }
}
