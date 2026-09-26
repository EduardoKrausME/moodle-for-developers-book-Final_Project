# Moodle Checkpoint activity

`mod_checkpoint` is the final project from Chapter 30 of *Moodle for Developers*. It is a deliberately compact activity module that demonstrates how production Moodle APIs fit together without turning a teaching example into an artificial framework.

A teacher creates a checkpoint with instructions, a due date, a maximum grade, and the accepted evidence types. A learner submits text, one file, or both. A teacher reviews the submission, records a grade and feedback, and can reopen a graded attempt. The plugin publishes grades to Gradebook, exposes custom completion rules, sends grade notifications through an adhoc task, participates in Moodle 5 activity overview, exports and deletes personal data, and supports course backup and restore.

## Requirements

- Moodle 5.0 to 5.2
- PHP version supported by the selected Moodle release
- Moodle cron configured when grade notifications are enabled

The plugin metadata requires Moodle `2025041400` or newer and declares support through Moodle 5.2.

## Installation

Clone or extract this repository as the `checkpoint` activity directory:

```text
public/mod/checkpoint
```

Then visit Site administration > Notifications or run the normal CLI upgrade process.

## Main architecture

The activity configuration is stored in `checkpoint`. The current learner submission is stored in `checkpoint_submission`, with a unique constraint on `(checkpointid, userid)`. The submission table stores the domain grade and feedback; Moodle Gradebook remains the official course grade projection and can be rebuilt from the submission data.

Important responsibilities are separated as follows:

- `classes/local/manager.php`: state transitions and domain orchestration.
- `classes/form/`: learner submission and teacher grading forms.
- `classes/completion/custom_completion.php`: custom completion rules.
- `classes/courseformat/overview.php`: Moodle 5 activity overview integration.
- `classes/external/`: AJAX/Web Service read endpoints with context and capability validation.
- `classes/event/`: submission and grading domain events.
- `classes/task/send_grade_notification.php`: asynchronous grade notification.
- `classes/privacy/provider.php`: metadata, context discovery, export, and deletion.
- `backup/moodle2/`: backup and restore, including user mapping and evidence files.

The application service receives Moodle's database and clock services by dependency injection. Deadline-sensitive logic therefore does not need direct `time()` calls inside the domain service.

## Capabilities

- `mod/checkpoint:addinstance`: create checkpoint activities.
- `mod/checkpoint:view`: open the activity.
- `mod/checkpoint:submit`: submit evidence for the current user.
- `mod/checkpoint:grade`: view evidence and grade submissions.
- `mod/checkpoint:manage`: administrative operations that may act across users.

All entry points validate the module context. File delivery additionally verifies that the current user owns the submission or can grade it.

## Files

Learner evidence uses the Moodle Files API with this layout:

```text
context   = module context
component = mod_checkpoint
filearea  = evidence
itemid    = checkpoint_submission.id
```

The file area accepts one file per current submission. Files are served only through `checkpoint_pluginfile()`.

## Gradebook and completion

The module creates one numeric grade item with a minimum of zero and the instance maximum grade. `checkpoint_update_grades()` can rebuild Gradebook from graded submissions.

Two custom completion rules are available:

- learner has formally submitted evidence;
- submission has been graded.

Reopening a submission clears its domain grade and publishes a null grade to Gradebook, which also causes the custom grading completion rule to become incomplete again.

## External functions

`mod_checkpoint_get_status` is AJAX-enabled and restricted to graders. It returns small cached counters for the teacher dashboard.

`mod_checkpoint_get_own_status` returns only the authenticated learner's own submission state and uses the submission capability rather than reusing the teacher endpoint.

## Notifications

When enabled in plugin settings, grading queues `send_grade_notification` as an adhoc task. The task reconstructs all required data from the submission id and records `notificationtime` after a successful send so a normal retry after completion does not send a duplicate message.

## Privacy

The privacy provider declares submission text, status, grade, feedback, grader references, timestamps, and evidence files. Learner-owned submissions can be exported and deleted. When a grader account is erased without owning the submission, the grader reference is anonymised rather than deleting another learner's submission.

## Backup and restore

Activity configuration is always backed up. Submissions are included only when user information is enabled. Learner and grader ids are annotated as user ids, evidence files use the submission id as their item id, and restore records a submission mapping before restoring evidence files.

## Tests

The repository includes PHPUnit coverage for submission flow, negative capability checks, events, Gradebook, completion, Privacy API, and notification task idempotence, plus Behat coverage for the learner/teacher workflow and a learner UI permission check.

The existing GitHub Actions workflow runs the plugin against Moodle 5.1 and 5.2 using PostgreSQL and MariaDB, with PHP lint, Moodle Code Checker, Plugin Validate, savepoint validation, PHPUnit, and Behat.

## Release checklist

Before publishing a release, install the generated plugin package on a clean Moodle instance, test upgrade from the previous real release once one exists, and restore a course backup into a separate installation. A repository checkout passing tests is not a substitute for testing the package that will actually be distributed.
