@mod @mod_room @mod_room_distinguish_course @street_college
Feature: Distinguish between courses
  In order to understand which slots apply in a particular context
  As a participant
  I need to be shown only slots which are applicable to the context of the room plan I am viewing

  Scenario: See booked slots in the room plan
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | teacher1 | Teacher   | One      | teacher1@e.mail |
      | student1 | Student   | One      | student1@e.mail |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
      | Course 2 | C2        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student1 | C2     | student        |
    And the following "activities" exist:
      | activity | name      | course | idnumber  |
      | room     | Room Plan | C1     | roomplan  |
      | room     | Room Plan | C2     | roomplan2 |
    And the following rooms are defined in the room module:
      | roomname |
      | The Room |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Event in course 1 |
      | Room | The Room |
      | starttime[day] | 30 |
      | starttime[month] | October |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"
    And I am on "Course 2" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Course 2 thing |
      | Room | The Room |
      | starttime[day] | 30 |
      | starttime[month] | October |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I set the following fields to these values:
      | displaydate[day] | 30 |
      | displaydate[month] | October |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should see "Event in course 1"
    And I should not see "Course 2 thing"
    When I am on "Course 2" course homepage
    And I follow "Room Plan"
    And I set the following fields to these values:
      | displaydate[day] | 30 |
      | displaydate[month] | October |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should not see "Event in course 1"
    And I should see "Course 2 thing"
