@mod @mod_room @mod_room_type_master @street_college
Feature: Master plans
  In order to have an overview of the use of physical spaces
  As a manager of my institution
  I want to see all the slots taking place in the institution

  Background:
    Given the following "courses" exist:
      | fullname   | shortname |
      | greek      | gk        |
      | tilde      | ti        |
      | Greatnuss  | Gn        |
      | Oport      | O         |
      | Staff Room | staff     |
    And the following "activities" exist:
      | activity | name           | course | idnumber |
      | room     | greek plan     | gk     | gkplan   |
      | room     | tildeplan      | ti     | tiplan   |
      | room     | The Great Nuss | Gn     | gnplan   |
      | room     | OPLAN          | O      | oplan    |
    And the following rooms are defined in the room module:
      | roomname       |
      | A Room         |
      | Big            |
      | Morning room   |
      | Stunning space |
    And the following slots are defined in the room module:
      | roomplan       | slottitle   | room           | starttime        | duration | spots |
      | greek plan     | greek class | Stunning space | 2019-02-27 11:00 | 4:00     | 8     |
      | tildeplan      | use tilde   | Big            | 2019-02-27 10:00 |          |       |
      | The Great Nuss |             | A Room         | 2022-01-01 14:30 |          | 2     |
      | OPLAN          | OOOOO       | Stunning space | 2022-01-01 16:00 | 2:20     | 1     |
    And I log in as "admin"
    And I am on "Staff Room" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Master Plan |
      | Plan type    | Master      |
    When I follow "Master Plan"

  Scenario: View all slots
    Then I should see "Master room plan"
    When I set the following fields to these values:
      | displaydate[day]   | 27       |
      | displaydate[month] | February |
      | displaydate[year]  | 2019     |
    And I press "Display"
    Then I should see "greek: greek class"
    And I should see "tilde: use tilde"
    When I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | January |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then "[data-event-title=\"Greatnuss\"]" "css_element" should exist
    And I should see "Oport: OOOOO"

  # TODO: Test that slots in a course category are prefixed as such

  Scenario: Delete a slot via the slotedit page
    Given I set the following fields to these values:
      | displaydate[day]   | 27       |
      | displaydate[month] | February |
      | displaydate[year]  | 2019     |
    And I press "Display"
    And I click on "[data-event-title=\"greek class\"][data-action=\"edit\"]" "css_element"
    When I press "Delete slot"
    And I press "Confirm delete"
    Then I should not see "greek: greek class"
    And I should see "tilde: use tilde"
