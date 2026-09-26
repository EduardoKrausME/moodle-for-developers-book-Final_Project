<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Activity configuration form for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Checkpoint module form.
 */
class mod_checkpoint_mod_form extends moodleform_mod {
    /**
     * Define instance fields.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('checkpointname', 'mod_checkpoint'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('date_time_selector', 'duedate', get_string('duedate', 'mod_checkpoint'), [
            'optional' => true,
        ]);

        $mform->addElement('advcheckbox', 'allowtext', get_string('allowtext', 'mod_checkpoint'));
        $mform->setDefault('allowtext', 1);

        $mform->addElement('advcheckbox', 'allowfile', get_string('allowfile', 'mod_checkpoint'));
        $mform->setDefault('allowfile', 1);

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Add custom completion rules.
     *
     * @return string[]
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $suffix = $this->get_suffix();

        $submitfield = 'completionsubmit' . $suffix;
        $gradefield = 'completiongrade' . $suffix;

        $mform->addElement('advcheckbox', $submitfield, '', get_string('completiondetail:submit', 'mod_checkpoint'));
        $mform->addElement('advcheckbox', $gradefield, '', get_string('completiondetail:grade', 'mod_checkpoint'));

        return [$submitfield, $gradefield];
    }

    /**
     * Determine whether at least one custom completion rule is enabled.
     *
     * @param array $data Form data.
     * @return bool
     */
    public function completion_rule_enabled($data): bool {
        $suffix = $this->get_suffix();
        return !empty($data['completionsubmit' . $suffix]) || !empty($data['completiongrade' . $suffix]);
    }

    /**
     * Validate activity configuration.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (empty($data['allowtext']) && empty($data['allowfile'])) {
            $errors['allowtext'] = get_string('error:nosubmissiontype', 'mod_checkpoint');
        }
        return $errors;
    }
}
