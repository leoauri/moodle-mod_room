@mod @mod_room @mod_room_slot_save_as_new @street_college
Feature: Save slot as new
  In order to make organising events more efficient and accurate
  As an organiser
  I need to be able to base a new slot on the settings of an existing one

  Scenario: Save an edited slot as a new one
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
      | Diplom 2     | Dip2      | audio    |
      | Music Theory | mustheory | music    |
      | Gardening    | garden    | 0        |
    And the following "activities" exist:
      | activity | name       | course    | idnumber  |
      | room     | singplan   | sing      | singplan  |
      | room     | pianoplan  | pno       | pianoplan |
      | room     | diplomplan | Dip1      | diplan    |
      | room     | dip2plan   | Dip2      | diplan2   |
      | room     | theoryplan | mustheory | thplan    |
      | room     | gardenplan | garden    | gaplan    |
    And the following rooms are defined in the room module:
      | roomname |
      | Room     |
    And the following slots are defined in the room module:
      | roomplan   | slottitle    | room | starttime        | duration | spots | context          |
      | diplomplan | First Lesson | Room | 2023-08-20 11:00 | 2:00     | 4     | audioengineering |
    And I log in as "admin"
    And I am on "Diplom 2" course homepage
    And I follow "dip2plan"
    And I set the following fields to these values:
      | displaydate[day]   | 20     |
      | displaydate[month] | August |
      | displaydate[year]  | 2023   |
    And I press "Display"
    And I click on "[data-event-title=\"First Lesson\"] [data-action=\"edit\"]" "css_element"
    When I set the following fields to these values:
      | starttime[hour] | 13 |
      | Slot title | Second Lesson |
    And I press "Save as new slot"
    Then I should see "Second Lesson"
    And I should see "First Lesson"
    And I should see "11:00 AM » 1:00 PM" in the "[data-event-title=\"First Lesson\"]" "css_element"
    And I should see "Free spots: 4" in the "[data-event-title=\"First Lesson\"]" "css_element"
    And I should see "Free spots: 4" in the "[data-event-title=\"Second Lesson\"]" "css_element"
    And I should see "1:00 PM » 3:00 PM" in the "[data-event-title=\"Second Lesson\"]" "css_element"
    When I click on "[data-event-title=\"Second Lesson\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | Context    | music          |
      | Slot title | Now everywhere |
      | Spots      | 1              |
    And I press "Save as new slot"
    Then I should see "Now everywhere"
    When I am on "Music Theory" course homepage
    And I follow "theoryplan"
    And I set the following fields to these values:
      | displaydate[day]   | 20     |
      | displaydate[month] | August |
      | displaydate[year]  | 2023   |
    And I press "Display"
    Then I should see "Now everywhere"
    And I should see "1:00 PM » 3:00 PM" in the "[data-event-title=\"Now everywhere\"]" "css_element"
    And I should see "Free spots: 1" in the "[data-event-title=\"Now everywhere\"]" "css_element"
    And I should see "Room" in the "[data-event-title=\"Now everywhere\"]" "css_element"
