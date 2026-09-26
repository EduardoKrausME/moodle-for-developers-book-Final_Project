<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * English language strings for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Checkpoint';
$string['pluginadministration'] = 'Checkpoint administration';
$string['modulename'] = 'Checkpoint';
$string['modulenameplural'] = 'Checkpoints';
$string['modulename_help'] = 'Use a checkpoint when learners must submit a short piece of evidence, ' .
    'optionally with one file, for teacher review and grading.';
$string['checkpointname'] = 'Checkpoint name';
$string['checkpoint:addinstance'] = 'Add a new checkpoint';
$string['checkpoint:view'] = 'View checkpoint';
$string['checkpoint:submit'] = 'Submit checkpoint evidence';
$string['checkpoint:grade'] = 'Grade checkpoint submissions';
$string['checkpoint:manage'] = 'Manage checkpoint submissions';

$string['allowtext'] = 'Allow text evidence';
$string['allowfile'] = 'Allow one evidence file';
$string['duedate'] = 'Due date';
$string['duedatevalue'] = 'Due date: {$a}';
$string['maxbytes'] = 'Maximum evidence file size';
$string['maxbytes_desc'] = 'Global upper limit for one checkpoint evidence file. Course and site upload limits still apply.';
$string['notifygrade'] = 'Notify learners after grading';
$string['notifygrade_desc'] = 'Queue an asynchronous Moodle notification when a checkpoint submission is graded.';

$string['submissiontext'] = 'Text evidence';
$string['evidence'] = 'Evidence file';
$string['editsubmission'] = 'Edit submission';
$string['savesubmission'] = 'Save submission';
$string['submissionsaved'] = 'Checkpoint submission saved.';
$string['submissionreopened'] = 'The submission was reopened.';
$string['submissions'] = 'Submissions';
$string['viewsubmissions'] = 'View submissions';
$string['submissionstatus'] = 'Submission status';
$string['pendingsubmissions'] = 'Pending submissions';
$string['student'] = 'Student';
$string['filterbystatus'] = 'Filter by status';
$string['summarycounts'] = 'Submitted: {$a->submitted} · Graded: {$a->graded} · Reopened: {$a->reopened} · Late: {$a->late}';

$string['status:none'] = 'Not submitted';
$string['status:draft'] = 'Draft';
$string['status:submitted'] = 'Submitted';
$string['status:graded'] = 'Graded';
$string['status:reopened'] = 'Reopened';
$string['late'] = 'Late';
$string['ontime'] = 'On time';
$string['deadline'] = 'Deadline status';

$string['grade'] = 'Grade';
$string['gradeverb'] = 'Grade';
$string['feedback'] = 'Feedback';
$string['savegrade'] = 'Save grade';
$string['gradesaved'] = 'Grade and feedback saved.';
$string['gradefor'] = 'Grade submission for {$a}';
$string['graderange'] = 'Valid range: 0 to {$a}';
$string['reopen'] = 'Reopen';

$string['completiondetail:submit'] = 'Learner must submit evidence';
$string['completiondetail:grade'] = 'Submission must be graded';

$string['error:nosubmissiontype'] = 'Enable text evidence, file evidence, or both.';
$string['error:emptysubmission'] = 'Add text evidence or an evidence file before submitting.';
$string['error:gradedlocked'] = 'This submission has already been graded. A teacher must reopen it before it can be edited.';
$string['error:cannotothersubmit'] = 'You cannot submit evidence on behalf of another user.';
$string['error:invalidgrader'] = 'The grader does not match the authenticated user.';
$string['error:graderange'] = 'The grade must be between 0 and {$a}.';
$string['error:invalidsubmission'] = 'The requested submission does not belong to this checkpoint.';

$string['eventsubmissioncreated'] = 'Checkpoint submission created or updated';
$string['eventsubmissiongraded'] = 'Checkpoint submission graded';

$string['messageprovider:graded'] = 'Checkpoint grading notifications';
$string['notification:subject'] = '{$a} has been graded';
$string['notification:body'] = 'Your checkpoint "{$a->checkpoint}" has been graded: {$a->grade} / {$a->maxgrade}.';
$string['notification:small'] = 'Your checkpoint has been graded.';

$string['privacy:metadata:submission'] = 'The current checkpoint submission, grading data, and timestamps.';
$string['privacy:metadata:submission:checkpointid'] = 'The checkpoint receiving the submission.';
$string['privacy:metadata:submission:userid'] = 'The user who owns the submission.';
$string['privacy:metadata:submission:status'] = 'The current submission state.';
$string['privacy:metadata:submission:text'] = 'The text evidence submitted by the user.';
$string['privacy:metadata:submission:grade'] = 'The grade assigned to the submission.';
$string['privacy:metadata:submission:feedback'] = 'Teacher feedback for the submission.';
$string['privacy:metadata:submission:graderid'] = 'The user who graded the submission.';
$string['privacy:metadata:submission:timecreated'] = 'When the submission was created.';
$string['privacy:metadata:submission:timemodified'] = 'When the submission was last changed.';
$string['privacy:metadata:submission:timegraded'] = 'When the submission was graded.';
$string['privacy:metadata:files'] = 'Evidence files submitted by learners are stored by the Moodle Files API.';
$string['privacy:path:submission'] = 'Submission';
$string['privacy:path:grading'] = 'Grading activity';
