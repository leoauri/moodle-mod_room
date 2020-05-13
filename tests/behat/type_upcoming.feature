@mod @mod_room @mod_room_type_upcoming @street_college
Feature: Upcoming slots plans
  In order to plan my involvement in the institution
  As a student
  I need to see all upcoming slots available to me in a course

  Scenario: View all upcoming slots
    Given the following "courses" exist:
      | fullname   | shortname |
      | greek      | gk        |
      | tilde      | ti        |
    And the following "activities" exist:
      | activity | name           | course | idnumber |
      | room     | greek plan     | gk     | gkplan   |
      | room     | tildeplan      | ti     | tiplan   |
    And the following rooms are defined in the room module:
      | roomname       |
      | A Room         |
      | Big            |
    And the following slots are defined in the room module:
      | roomplan   | slottitle | room   | starttime        | duration | spots |
      | greek plan | upcoming2 | Big    | 2030-01-02 16:00 | 1:00     | 4     |
      | greek plan | upcoming1 | Big    | 2030-01-01 16:00 |          |       |
      | greek plan | upcoming3 | Big    | 2035-01-01 11:00 | 2:30     | 1     |
      | greek plan | upcoming4 | A Room | 2036-01-01 16:00 |          |       |
      | tildeplan  | upcoming5 | A Room | 2036-01-01 16:00 |          |       |
    And I log in as "admin"
    And I am on "greek" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Upcoming Plan |
      | Plan type    | Upcoming      |
    And I follow "Upcoming Plan"
    Then "#fitem_id_displaydate" "css_element" should not exist
    And I should see "Upcoming slots"
    And I should see "upcoming1"
    And I should see "upcoming2"
    And I should see "upcoming3"
    And I should see "upcoming4"
    And I should not see "upcoming5"
    And "upcoming1" "text" should appear before "upcoming2" "text"

  Scenario: Create and view slots on consecutive days
    Given the following "courses" exist:
      | fullname   | shortname |
      | greek      | gk        |
    And the following rooms are defined in the room module:
      | roomname       |
      | A Room         |
    And I log in as "admin"
    And I am on "greek" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Upcoming Plan |
      | Plan type    | Upcoming      |
    And I follow "Upcoming Plan"
    And I follow "Add slot"
    When I set the following fields to these values:
      | Slot title        | First celebration |
      | Room              | A Room            |
      | starttime[day]    | 1                 |
      | starttime[month]  | October           |
      | starttime[year]   | 2030              |
      | starttime[hour]   | 11                |
      | starttime[minute] | 00                |
      | duration[hours]   | 2                 |
      | duration[minutes] | 30                |
    And I press "Add slot"
    Then I should see "First celebration"
    When I follow "Add slot"
    And I set the following fields to these values:
      | Slot title        | Second celebration |
      | Room              | A Room             |
      | starttime[day]    | 2                  |
      | starttime[month]  | October            |
      | starttime[year]   | 2030               |
      | starttime[hour]   | 11                 |
      | starttime[minute] | 00                 |
      | duration[hours]   | 2                  |
      | duration[minutes] | 30                 |
    And I press "Add slot"
    Then I should see "Second celebration"
    And I should see "First celebration"
    When I follow "Add slot"
    And I set the following fields to these values:
      | Slot title        | Third celebration |
      | Room              | A Room            |
      | starttime[day]    | 2                 |
      | starttime[month]  | October           |
      | starttime[year]   | 2035              |
      | starttime[hour]   | 11                |
      | starttime[minute] | 00                |
      | duration[hours]   | 2                 |
      | duration[minutes] | 30                |
    And I press "Add slot"
    Then I should see "Third celebration"
    And I should see "Second celebration"
    And I should see "First celebration"
