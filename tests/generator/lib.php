<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Data generator for mod_checkpoint tests.
 *
 * @package    mod_checkpoint
 * @category   test
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_checkpoint_generator extends testing_module_generator {
    /**
     * Create a checkpoint instance with useful defaults.
     *
     * @param stdClass|array|null $record Values overriding defaults.
     * @param array|null $options Generator options.
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null): stdClass {
        $record = (array)$record;
        $record += [
            'name' => 'Test checkpoint',
            'intro' => 'Submit evidence for this checkpoint.',
            'introformat' => FORMAT_HTML,
            'duedate' => 0,
            'grade' => 100,
            'allowtext' => 1,
            'allowfile' => 1,
            'completionsubmit' => 0,
            'completiongrade' => 0,
        ];
        return parent::create_instance($record, $options);
    }
}
