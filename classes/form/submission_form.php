<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint\form;

/**
 * Learner submission form.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission_form extends \moodleform {
    /**
     * Define submission controls.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;
        $checkpoint = $this->_customdata['checkpoint'];
        $fileoptions = $this->_customdata['fileoptions'];

        if ($checkpoint->allowtext) {
            $mform->addElement('editor', 'submissiontext', get_string('submissiontext', 'mod_checkpoint'), null, [
                'maxfiles' => 0,
                'context' => $this->_customdata['context'],
            ]);
            $mform->setType('submissiontext', PARAM_RAW);
        }

        if ($checkpoint->allowfile) {
            $mform->addElement(
                'filemanager',
                'evidence_filemanager',
                get_string('evidence', 'mod_checkpoint'),
                null,
                $fileoptions,
            );
        }

        $this->add_action_buttons(true, get_string('savesubmission', 'mod_checkpoint'));
    }
}
