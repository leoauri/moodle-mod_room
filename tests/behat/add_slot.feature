@mod @mod_room @mod_room_add_slot @street_college
Feature: Add slots
  In order to organise use of physical spaces
  As a teacher
  I need to add slots to the room plan

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | teacher1 | Teacher   | One      | teacher1@e.mail   |
      | student1 | Student   | One      | student1@e.mail   |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Room Plan |
    And I follow "Room Plan"
    And I navigate to "New room" in current page administration
    And I set the following fields to these values:
      | Room name | The Room |
    And I press "Add room"
    And I log out

  Scenario: Admin can add a slot to the room plan
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot name | Nice event |
      | Room | The Room |
    And I press "Add slot"
    When I am viewing site calendar
    Then I should see "Nice event"
    And I should see "The Room"

  Scenario: A teacher can add a slot to the room plan
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot name | Nice event |
      | Room | The Room |
    And I press "Add slot"
    When I am viewing site calendar
    Then I should see "Nice event"
    And I should see "The Room"

  Scenario: A student does not have a link for adding new slots
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Room Plan"
    Then I should not see "Add slot"

  Scenario: A student can see slots added by a teacher
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot name | Nice event |
      | Room | The Room |
    And I press "Add slot"
    And I log out
    And I log in as "student1"
    When I am viewing site calendar
    Then I should see "Nice event"
    And I should see "The Room"
