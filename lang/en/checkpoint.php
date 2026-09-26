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
 * English language strings for mod_checkpoint.
 *
 * @package    mod_checkpoint
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowfile'] = 'Allow one evidence file';
$string['allowtext'] = 'Allow text evidence';
$string['checkpoint:addinstance'] = 'Add a new checkpoint';
$string['checkpoint:grade'] = 'Grade checkpoint submissions';
$string['checkpoint:manage'] = 'Manage checkpoint submissions';
$string['checkpoint:submit'] = 'Submit checkpoint evidence';
$string['checkpoint:view'] = 'View checkpoint';
$string['checkpointname'] = 'Checkpoint name';
$string['completiondetail:grade'] = 'Submission must be graded';
$string['completiondetail:submit'] = 'Learner must submit evidence';
$string['deadline'] = 'Deadline status';
$string['duedate'] = 'Due date';
$string['duedatevalue'] = 'Due date: {$a}';
$string['editsubmission'] = 'Edit submission';
$string['error:cannotothersubmit'] = 'You cannot submit evidence on behalf of another user.';
$string['error:emptysubmission'] = 'Add text evidence or an evidence file before submitting.';
$string['error:gradedlocked'] = 'This submission has already been graded. A teacher must reopen it before it can be edited.';
$string['error:graderange'] = 'The grade must be between 0 and {$a}.';
$string['error:invalidgrader'] = 'The grader does not match the authenticated user.';
$string['error:invalidsubmission'] = 'The requested submission does not belong to this checkpoint.';
$string['error:nosubmissiontype'] = 'Enable text evidence, file evidence, or both.';
$string['eventsubmissioncreated'] = 'Checkpoint submission created or updated';
$string['eventsubmissiongraded'] = 'Checkpoint submission graded';
$string['evidence'] = 'Evidence file';
$string['feedback'] = 'Feedback';
$string['filterbystatus'] = 'Filter by status';
$string['grade'] = 'Grade';
$string['gradefor'] = 'Grade submission for {$a}';
$string['graderange'] = 'Valid range: 0 to {$a}';
$string['gradesaved'] = 'Grade and feedback saved.';
$string['gradeverb'] = 'Grade';
$string['late'] = 'Late';
$string['maxbytes'] = 'Maximum evidence file size';
$string['maxbytes_desc'] = 'Global upper limit for one checkpoint evidence file. Course and site upload limits still apply.';
$string['messageprovider:graded'] = 'Checkpoint grading notifications';
$string['modulename'] = 'Checkpoint';
$string['modulename_help'] = 'Use a checkpoint when learners must submit a short piece of evidence, optionally with one file, for teacher review and grading.';
$string['modulenameplural'] = 'Checkpoints';
$string['notification:body'] = 'Your checkpoint "{$a->checkpoint}" has been graded: {$a->grade} / {$a->maxgrade}.';
$string['notification:small'] = 'Your checkpoint has been graded.';
$string['notification:subject'] = '{$a} has been graded';
$string['notifygrade'] = 'Notify learners after grading';
$string['notifygrade_desc'] = 'Queue an asynchronous Moodle notification when a checkpoint submission is graded.';
$string['ontime'] = 'On time';
$string['pendingsubmissions'] = 'Pending submissions';
$string['pluginadministration'] = 'Checkpoint administration';
$string['pluginname'] = 'Checkpoint';
$string['privacy:metadata:files'] = 'Evidence files submitted by learners are stored by the Moodle Files API.';
$string['privacy:metadata:submission'] = 'The current checkpoint submission, grading data, and timestamps.';
$string['privacy:metadata:submission:checkpointid'] = 'The checkpoint receiving the submission.';
$string['privacy:metadata:submission:feedback'] = 'Teacher feedback for the submission.';
$string['privacy:metadata:submission:grade'] = 'The grade assigned to the submission.';
$string['privacy:metadata:submission:graderid'] = 'The user who graded the submission.';
$string['privacy:metadata:submission:status'] = 'The current submission state.';
$string['privacy:metadata:submission:text'] = 'The text evidence submitted by the user.';
$string['privacy:metadata:submission:timecreated'] = 'When the submission was created.';
$string['privacy:metadata:submission:timegraded'] = 'When the submission was graded.';
$string['privacy:metadata:submission:timemodified'] = 'When the submission was last changed.';
$string['privacy:metadata:submission:userid'] = 'The user who owns the submission.';
$string['privacy:path:grading'] = 'Grading activity';
$string['privacy:path:submission'] = 'Submission';
$string['reopen'] = 'Reopen';
$string['savegrade'] = 'Save grade';
$string['savesubmission'] = 'Save submission';
$string['status:draft'] = 'Draft';
$string['status:graded'] = 'Graded';
$string['status:none'] = 'Not submitted';
$string['status:reopened'] = 'Reopened';
$string['status:submitted'] = 'Submitted';
$string['student'] = 'Student';
$string['submissionreopened'] = 'The submission was reopened.';
$string['submissions'] = 'Submissions';
$string['submissionsaved'] = 'Checkpoint submission saved.';
$string['submissionstatus'] = 'Submission status';
$string['submissiontext'] = 'Text evidence';
$string['summarycounts'] = 'Submitted: {$a->submitted} · Graded: {$a->graded} · Reopened: {$a->reopened} · Late: {$a->late}';
$string['viewsubmissions'] = 'View submissions';
