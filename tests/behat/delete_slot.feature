@mod @mod_room @mod_room_delete_slot @street_college
Feature: Add slots
  In order to correctly organise use of physical spaces
  As an organiser
  I need to be able to delete incorrectly entered slots

  Scenario: Delete slots from the room plan
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
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Wrong event |
      | Room | The Room |
      | starttime[day] | 1 |
      | starttime[month] | December |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Correct event |
      | Room | The Room |
      | starttime[day] | 1 |
      | starttime[month] | December |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"
    And I set the following fields to these values:
      | displaydate[day] | 1 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    When I click on "[data-event-title=\"Wrong event\"] [data-action=\"delete\"]" "css_element"
    # Then I should see "Wrong event"
    # And I should see "The Room"
    # And I should see "1 December 2019, 12:30 PM"
    When I press "Confirm delete"
    Then I should see "Correct event"
    And I should not see "Wrong event"