@mod @mod_room @mod_room_slot_add @street_college
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
      | admin    | C1     | manager |
    And the following "activities" exist:
      | activity | name      | course | idnumber |
      | room     | Room Plan | C1     | roomplan |
    And the following rooms are defined in the room module:
      | roomname |
      | The Room |

  Scenario: Give slots a duration with hours and minutes
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    When I set the following fields to these values:
      | Slot title | Duration celebration |
      | Room | The Room |
      | starttime[day] | 1 |
      | starttime[month] | October |
      | starttime[year] | 2020 |
      | starttime[hour] | 11 |
      | starttime[minute] | 00 |
      | duration[hours] | 2 |
      | duration[minutes] | 30 |
    And I press "Add slot"
    Then I should see "1 October 2020, 11:00 AM » 1:30 PM"

  Scenario: Admin can add a slot to the room plan
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Nice event |
      | Room | The Room |
    And I press "Add slot"
    When I am viewing site calendar
    Then I should see "Nice event"
    When I follow "Nice event"
    Then I should see "The Room"

  Scenario: A teacher can add a slot to the room plan
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Nice event |
      | Room | The Room |
    And I press "Add slot"
    When I am viewing site calendar
    Then I should see "Nice event"
    When I follow "Nice event"
    Then I should see "The Room"

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
      | Slot title | Nice event |
      | Room | The Room |
    And I press "Add slot"
    And I log out
    And I log in as "student1"
    When I am viewing site calendar
    Then I should see "Nice event"
    When I follow "Nice event"
    Then I should see "The Room"
