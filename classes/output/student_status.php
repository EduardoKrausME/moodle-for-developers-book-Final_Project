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
class student_status implements renderable, templatable {
    /**
     * Checkpoint record.
     *
     * @var \stdClass
     */
    private readonly \stdClass $checkpoint;

    /**
     * Current submission record.
     *
     * @var \stdClass|null
     */
    private readonly ?\stdClass $submission;

    /**
     * Whether the learner may edit the submission.
     *
     * @var bool
     */
    private readonly bool $canedit;

    /**
     * Submission form URL.
     *
     * @var string
     */
    private readonly string $editurl;

    /**
     * Whether the current submission is late.
     *
     * @var bool
     */
    private readonly bool $late;

    /**
     * Evidence file links.
     *
     * @var array
     */
    private readonly array $files;

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
        \stdClass $checkpoint,
        ?\stdClass $submission,
        bool $canedit,
        string $editurl,
        bool $late = false,
        array $files = [],
    ) {
        $this->checkpoint = $checkpoint;
        $this->submission = $submission;
        $this->canedit = $canedit;
        $this->editurl = $editurl;
        $this->late = $late;
        $this->files = $files;
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
