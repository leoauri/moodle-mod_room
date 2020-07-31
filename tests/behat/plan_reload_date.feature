@mod @mod_room @mod_room_plan_reload_date @street_college @javascript
Feature: Reload plan on date change
  In order to intuitively navigate between dates
  As a user of the room plan module
  I need the room plan to refresh to reflect changes in the selected date

  Scenario: Reload a room plan on date change
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "activities" exist:
      | activity | name      | course | idnumber  |
      | room     | Room Plan | C1     | roomplan  |
    And the following rooms are defined in the room module:
      | roomname |
      | The Room |
    And the following slots are defined in the room module:
      | roomplan  | slottitle       | room     | starttime        | duration | spots | context |
      | Room Plan | first day slot  | The Room | 2030-07-16 10:00 |          |       |         |
      | Room Plan | second day slot | The Room | 2030-07-17 14:00 |          |       |         |
      | Room Plan | third day slot  | The Room | 2030-08-17 10:00 |          |       |         |
      | Room Plan | fourth day slot | The Room | 2029-08-17 18:30 |          |       |         |
    And I log in as "admin"
    When I view "roomplan" room module for date "2020-07-31"
    Then "#id_submitbutton" "css_element" should not be visible
    When I set the following fields to these values:
      | displaydate[day]   | 16   |
      | displaydate[month] | July |
      | displaydate[year]  | 2030 |
    Then I should see "first day slot"
    When I set the following fields to these values:
      | displaydate[day] | 17 |
    Then I should see "second day slot"
    When I set the following fields to these values:
      | displaydate[month] | August |
    Then I should see "third day slot"
    When I set the following fields to these values:
      | displaydate[year] | 2029 |
    Then I should see "fourth day slot"
