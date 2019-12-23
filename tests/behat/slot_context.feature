@mod @mod_room @mod_room_slot_context @street_college
Feature: Slot context
  In order to organise events spanning several courses or the entire institution
  As an organiser
  I need to be able to create slots in a course, category, or site context

  Background:
    Given the following "categories" exist:
      | name             | category | idnumber |
      | music            | 0        | music    |
      | audioengineering | music    | audio    |
      | instrumental     | music    | inst     |
    And the following "courses" exist:
      | fullname     | shortname | category |
      | singing      | sing      | inst     |
      | piano        | pno       | inst     |
      | Diplom 1     | Dip1      | audio    |
      | Music Theory | mustheory | music    |
      | Gardening    | garden    | 0        |
    And the following "activities" exist:
      | activity | name       | course    | idnumber  |
      | room     | singplan   | sing      | singplan  |
      | room     | pianoplan  | pno       | pianoplan |
      | room     | diplomplan | Dip1      | diplan    |
      | room     | theoryplan | mustheory | thplan    |
      | room     | gardenplan | garden    | gaplan    |
    And the following rooms are defined in the room module:
      | roomname |
      | Room     |

  Scenario: Duplicate slots with context
    Given the following slots are defined in the room module:
      | roomplan  | slottitle     | room | starttime        | duration | spots | context      |
      | singplan  | theorylesson  | Room | 2022-01-01 23:30 | 2:00     | 1     | music        |
      | pianoplan | play together | Room | 2022-01-02 09:30 | 3:00     | 5     | instrumental |
    And I log in as "admin"
    When I am on "Diplom 1" course homepage
    And I follow "diplomplan"
    And I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | January |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "theorylesson"
    And I should see "Saturday, 1 January 2022, 11:30 PM » Sunday, 2 January 2022, 1:30 AM"
    When I am on "singing" course homepage
    And I follow "singplan"
    And I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | January |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "theorylesson"
    When I navigate to "Slot duplication tool" in current page administration
    And I set the following fields to these values:
      | startdate[day]   | 1       |
      | startdate[month] | January |
      | startdate[year]  | 2022    |
      | enddate[day]     | 2       |
      | enddate[month]   | January |
      | enddate[year]    | 2022    |
    And I press "Duplicate"
    Then I should see "theorylesson"
    And I should see "Saturday, 8 January 2022, 11:30 PM » Sunday, 9 January 2022, 1:30 AM"
    When I am on "Diplom 1" course homepage
    And I follow "diplomplan"
    And I set the following fields to these values:
      | displaydate[day]   | 8       |
      | displaydate[month] | January |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "theorylesson"
    And I should see "Saturday, 8 January 2022, 11:30 PM » Sunday, 9 January 2022, 1:30 AM"
    When I am on "singing" course homepage
    And I follow "singplan"
    And I set the following fields to these values:
      | displaydate[day]   | 2       |
      | displaydate[month] | January |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "play together"
    And I should see "Free spots: 5"
    When I set the following fields to these values:
      | displaydate[day]   | 9       |
      | displaydate[month] | January |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "play together"
    And I should see "Free spots: 5"

  Scenario: View slot from various courses in a category
    And I log in as "admin"
    And I am on "singing" course homepage
    And I follow "singplan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Context           | instrumental      |
      | Slot title        | Instrument lesson |
      | Room              | Room              |
      | starttime[day]    | 1                 |
      | starttime[month]  | October           |
      | starttime[year]   | 2022              |
      | starttime[hour]   | 11                |
      | starttime[minute] | 00                |
      | duration[hours]   | 0                 |
      | duration[minutes] | 30                |
      | Spots             | 1                 |
    And I press "Add slot"
    When I am on "piano" course homepage
    And I follow "pianoplan"
    And I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | October |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "Instrument lesson"
    When I am on "Diplom 1" course homepage
    And I follow "diplomplan"
    And I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | October |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should not see "Instrument lesson"
    When I am on "piano" course homepage
    And I follow "pianoplan"
    And I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | October |
      | displaydate[year]  | 2022    |
    And I press "Display"
    When I click on "[data-event-title=\"Instrument lesson\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | Context | music |
    And I press "Update slot"
    And I am on "Diplom 1" course homepage
    And I follow "diplomplan"
    And I set the following fields to these values:
      | displaydate[day]   | 1       |
      | displaydate[month] | October |
      | displaydate[year]  | 2022    |
    And I press "Display"
    Then I should see "Instrument lesson"
