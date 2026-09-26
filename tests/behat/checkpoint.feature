@mod @mod_checkpoint
Feature: Submit and grade checkpoint evidence
  In order to verify progress inside a course
  As a learner and teacher
  I need to submit evidence and grade it through the activity workflow

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
      | teacher1 | Teacher | One | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Checkpoint course | CPOINT | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | CPOINT | student |
      | teacher1 | CPOINT | editingteacher |
    And the following "activities" exist:
      | activity | name | intro | course | idnumber | allowtext | allowfile | grade |
      | checkpoint | Project checkpoint | Submit your evidence | CPOINT | checkpoint1 | 1 | 0 | 100 |

  Scenario: Learner submits evidence and teacher grades it
    Given I log in as "student1"
    And I am on "Checkpoint course" course homepage
    When I follow "Project checkpoint"
    And I follow "Edit submission"
    And I set the field "Text evidence" to "My project evidence"
    And I press "Save submission"
    Then I should see "Checkpoint submission saved."
    And I should see "Submitted"
    And I log out

    When I log in as "teacher1"
    And I am on "Checkpoint course" course homepage
    And I follow "Project checkpoint"
    And I press "View submissions"
    And I click on "Grade" "link" in the "Student One" "table_row"
    And I set the field "Grade" to "92"
    And I set the field "Feedback" to "Good checkpoint"
    And I press "Save grade"
    Then I should see "Grade and feedback saved."
    And I log out

    When I log in as "student1"
    And I am on "Checkpoint course" course homepage
    And I follow "Project checkpoint"
    Then I should see "Graded"
    And I should see "92"
    And I should see "Good checkpoint"

  Scenario: Learner cannot see grading actions
    Given I log in as "student1"
    And I am on "Checkpoint course" course homepage
    When I follow "Project checkpoint"
    Then I should not see "View submissions"
