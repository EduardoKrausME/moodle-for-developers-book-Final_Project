<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * External function definitions for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_checkpoint_get_status' => [
        'classname' => 'mod_checkpoint\\external\\get_status',
        'description' => 'Return submission counters for a checkpoint.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/checkpoint:grade',
    ],
    'mod_checkpoint_get_own_status' => [
        'classname' => 'mod_checkpoint\\external\\get_own_status',
        'description' => 'Return the current user submission state for a checkpoint.',
        'type' => 'read',
        'capabilities' => 'mod/checkpoint:submit',
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
