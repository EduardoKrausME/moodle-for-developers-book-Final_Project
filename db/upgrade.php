<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Upgrade steps for mod_checkpoint.
 *
 * The initial release does not require a migration yet. This file exists from
 * version 1.0 so future schema changes have a documented upgrade path instead
 * of relying on install.xml edits alone.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_checkpoint.
 *
 * @param int $oldversion Installed plugin version.
 * @return bool
 */
function xmldb_checkpoint_upgrade(int $oldversion): bool {
    return true;
}
