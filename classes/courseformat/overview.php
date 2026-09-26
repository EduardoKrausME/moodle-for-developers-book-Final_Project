<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace mod_checkpointcourseformat;

use cm_info;
use core_calendaroutputhumandate;
use core_courseformatactivityoverviewbase;
use core_courseformatlocaloverviewoverviewitem;
use mod_checkpointlocalsubmission_status;

/**
 * Moodle activity overview integration for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class overview extends activityoverviewbase {
    /**
     * Create the overview integration with explicit dependencies.
     *
     * @param cm_info $cm Course module information.
     * @param moodle_database $db Moodle database connection.
     * @param coreclock $clock Moodle clock service.
     */
    public function __construct(
        cm_info $cm,
        private readonly moodle_database $db,
        private readonly coreclock $clock,
    ) {
        parent::__construct($cm);
    }

    /**
     * Return the configured due date.
     *
     * @return overviewitem|null
     */
    #[Override]
    public function get_due_date_overview(): ?overviewitem {
        $checkpoint = $this->db->get_record(
            'checkpoint',
            ['id' => $this->cm->instance],
            'id, duedate',
            MUST_EXIST,
        );

        return new overviewitem(
            name: get_string('duedate', 'mod_checkpoint'),
            value: $checkpoint->duedate ?: null,
            content: $checkpoint->duedate ? humandate::create_from_timestamp($checkpoint->duedate) : '-',
        );
    }

    /**
     * Return role-specific checkpoint status information.
     *
     * @return array
     */
    #[Override]
    public function get_extra_overview_items(): array {
        global $USER;

        if (has_capability('mod/checkpoint:grade', $this->context)) {
            $pending = $this->db->count_records('checkpoint_submission', [
                'checkpointid' => $this->cm->instance,
                'status' => submission_status::SUBMITTED,
            ]);
            return [
                'pendingsubmissions' => new overviewitem(
                    name: get_string('pendingsubmissions', 'mod_checkpoint'),
                    value: $pending,
                    content: $pending,
                ),
            ];
        }

        if (!has_capability('mod/checkpoint:submit', $this->context, $USER, false)) {
            return [];
        }

        $submission = $this->db->get_record('checkpoint_submission', [
            'checkpointid' => $this->cm->instance,
            'userid' => $USER->id,
        ]);
        $status = $submission
            ? get_string('status:' . $submission->status, 'mod_checkpoint')
            : get_string('status:none', 'mod_checkpoint');
        $items = [
            'submissionstatus' => new overviewitem(
                name: get_string('submissionstatus', 'mod_checkpoint'),
                value: $status,
                content: $status,
            ),
        ];

        $checkpoint = $this->db->get_record('checkpoint', ['id' => $this->cm->instance], 'id, duedate', MUST_EXIST);
        if ($submission && $checkpoint->duedate && $this->clock->time() > $checkpoint->duedate) {
            $items['deadline'] = new overviewitem(
                name: get_string('deadline', 'mod_checkpoint'),
                value: $submission->timemodified > $checkpoint->duedate ? 1 : 0,
                content: $submission->timemodified > $checkpoint->duedate
                    ? get_string('late', 'mod_checkpoint')
                    : get_string('ontime', 'mod_checkpoint'),
            );
        }

        return $items;
    }
}
