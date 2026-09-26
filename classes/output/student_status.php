<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpoint\output;

use renderable;
use renderer_base;
use templatable;

/**
 * View model for the learner checkpoint status card.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class student_status implements renderable, templatable {
    /**
     * Create the view model.
     *
     * @param \stdClass $checkpoint Checkpoint record.
     * @param \stdClass|null $submission Current submission.
     * @param bool $canedit Whether the learner may edit.
     * @param string $editurl Submission form URL.
     * @param bool $late Whether the current submission is late.
     * @param array $files Evidence file links.
     */
    public function __construct(
        private readonly \stdClass $checkpoint,
        private readonly ?\stdClass $submission,
        private readonly bool $canedit,
        private readonly string $editurl,
        private readonly bool $late = false,
        private readonly array $files = [],
    ) {
    }

    /**
     * Export template data.
     *
     * @param renderer_base $output Renderer.
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $status = $this->submission?->status ?? 'none';
        $feedback = '';
        $submissiontext = '';
        if ($this->submission) {
            $submissiontext = format_text(
                $this->submission->submissiontext,
                $this->submission->submissionformat,
                ['context' => $output->get_page()->context],
            );
            if ($this->submission->feedback !== '') {
                $feedback = format_text(
                    $this->submission->feedback,
                    $this->submission->feedbackformat,
                    ['context' => $output->get_page()->context],
                );
            }
        }

        return [
            'name' => format_string($this->checkpoint->name),
            'has_submission' => $this->submission !== null,
            'status' => get_string('status:' . $status, 'mod_checkpoint'),
            'statuskey' => $status,
            'can_edit' => $this->canedit,
            'editurl' => $this->editurl,
            'late' => $this->late,
            'submissiontext' => $submissiontext,
            'hastext' => $submissiontext !== '',
            'files' => $this->files,
            'hasfiles' => !empty($this->files),
            'graded' => $status === 'graded',
            'grade' => $this->submission?->grade,
            'grademax' => $this->checkpoint->grade,
            'feedback' => $feedback,
            'hasfeedback' => $feedback !== '',
        ];
    }
}
