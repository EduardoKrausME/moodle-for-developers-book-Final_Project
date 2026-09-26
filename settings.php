<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Administrative settings for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
        'mod_checkpoint/notifygrade',
        get_string('notifygrade', 'mod_checkpoint'),
        get_string('notifygrade_desc', 'mod_checkpoint'),
        1,
    ));

    $settings->add(new admin_setting_configselect(
        'mod_checkpoint/maxbytes',
        get_string('maxbytes', 'mod_checkpoint'),
        get_string('maxbytes_desc', 'mod_checkpoint'),
        0,
        get_max_upload_sizes($CFG->maxbytes),
    ));
}
