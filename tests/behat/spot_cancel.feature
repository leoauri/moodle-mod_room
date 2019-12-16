@mod @mod_room @mod_room_spot_cancel @street_college
Feature: Cancel slot
  In order to be able to change plans
  As a student
  I need to be able to cancel bookings I made

  Scenario: Cancel a booking
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | teacher1 | Teacher   | One      | teacher1@e.mail |
      | student1 | Student   | One      | student1@e.mail |
      | student2 | Student   | Two      | student2@e.mail |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "activities" exist:
      | activity | name      | course | idnumber |
      | room     | Room Plan | C1     | roomplan |
    And the following rooms are defined in the room module:
      | roomname |
      | The Room |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Nice event |
      | Room | The Room |
      | starttime[day] | 6 |
      | starttime[month] | November |
      | starttime[year] | 2050 |
      | starttime[hour] | 11 |
      | starttime[minute] | 30 |
      | Spots | 1 |
    And I press "Add slot"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I set the following fields to these values:
      | displaydate[day] | 6 |
      | displaydate[month] | November |
      | displaydate[year] | 2050 |
    And I press "Display"
    When I click on "[data-event-title=\"Nice event\"] [data-action=\"book-spot-button\"]" "css_element"
    Then I should see "Booked by: Student One" in the "[data-event-title=\"Nice event\"]" "css_element"
    When I click on "[data-event-title=\"Nice event\"] [data-action=\"booking-cancel-button\"]" "css_element"
    Then I should see "Free spots: 1" in the "[data-event-title=\"Nice event\"]" "css_element"
    And I should not see "Booked by: Student One" in the "[data-event-title=\"Nice event\"]" "css_element"
    And I should see "Booking cancelled"
    And I should not see "Spot booked"
    When I click on "[data-event-title=\"Nice event\"] [data-action=\"book-spot-button\"]" "css_element"
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I set the following fields to these values:
      | displaydate[day] | 6 |
      | displaydate[month] | November |
      | displaydate[year] | 2050 |
    And I press "Display"
    Then "[data-event-title=\"Nice event\"] [data-action=\"booking-cancel-button\"]" "css_element" should not exist