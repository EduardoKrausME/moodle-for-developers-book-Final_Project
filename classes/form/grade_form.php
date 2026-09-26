<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint\form;

/**
 * Teacher grading form.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_form extends \moodleform {
    /**
     * Define grading controls.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;
        $checkpoint = $this->_customdata['checkpoint'];

        $mform->addElement('text', 'grade', get_string('grade', 'mod_checkpoint'));
        $mform->setType('grade', PARAM_FLOAT);
        $mform->addRule('grade', null, 'required', null, 'client');

        $mform->addElement('static', 'graderange', '', get_string('graderange', 'mod_checkpoint', $checkpoint->grade));

        $mform->addElement('editor', 'feedback', get_string('feedback', 'mod_checkpoint'), null, [
            'maxfiles' => 0,
            'context' => $this->_customdata['context'],
        ]);
        $mform->setType('feedback', PARAM_RAW);

        $mform->addElement('hidden', 'submissionid');
        $mform->setType('submissionid', PARAM_INT);

        $this->add_action_buttons(true, get_string('savegrade', 'mod_checkpoint'));
    }

    /**
     * Validate grade bounds.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $checkpoint = $this->_customdata['checkpoint'];
        if (isset($data['grade']) && ((float)$data['grade'] < 0 || (float)$data['grade'] > (float)$checkpoint->grade)) {
            $errors['grade'] = get_string('error:graderange', 'mod_checkpoint', $checkpoint->grade);
        }
        return $errors;
    }
}
