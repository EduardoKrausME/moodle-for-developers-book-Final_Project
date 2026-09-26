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

        // The instance name is the label shown in the course section and gradebook item.
        $mform->addElement('text', 'name', get_string('checkpointname', 'mod_checkpoint'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Moodle owns intro file handling through the standard activity intro elements.
        $this->standard_intro_elements();

        // Due date is optional because some checkpoints are formative and not deadline based.
        $mform->addElement('date_time_selector', 'duedate', get_string('duedate', 'mod_checkpoint'), [
            'optional' => true,
        ]);

        // At least one submission type must remain enabled; validation enforces the combination.
        $mform->addElement('advcheckbox', 'allowtext', get_string('allowtext', 'mod_checkpoint'));
        $mform->setDefault('allowtext', 1);

        $mform->addElement('advcheckbox', 'allowfile', get_string('allowfile', 'mod_checkpoint'));
        $mform->setDefault('allowfile', 1);

        // Standard Moodle sections keep gradebook, availability and course module settings consistent.
        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Add custom completion rules.
     *
     * The field names must match the database columns and the custom_completion rule names.
     * Moodle stores these values directly on the activity instance during add/update.
     *
     * @return string[]
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;

        $mform->addElement('advcheckbox', 'completionsubmit', '', get_string('completiondetail:submit', 'mod_checkpoint'));
        $mform->addElement('advcheckbox', 'completiongrade', '', get_string('completiondetail:grade', 'mod_checkpoint'));

        return ['completionsubmit', 'completiongrade'];
    }

    /**
     * Determine whether at least one custom completion rule is enabled.
     *
     * @param array $data Form data.
     * @return bool
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionsubmit']) || !empty($data['completiongrade']);
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
