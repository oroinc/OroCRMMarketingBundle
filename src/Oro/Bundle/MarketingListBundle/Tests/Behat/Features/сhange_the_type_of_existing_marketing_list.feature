@regression
@ticket-BAP-20455

Feature: Change the type of existing marketing list
  Сheck whether it is possible to change the type of marketing list after saving

  Scenario: Create marketing list with 'Dynamic' type
    Given I login as administrator
    And go to Marketing/Marketing Lists
    And click "Create Marketing List"
    When I fill form with:
      | Name   | Marketing list |
      | Entity | Contact        |
      | Type   | Dynamic        |
    And add the following columns:
      | Primary Email |
    And save form
    Then I should see "Marketing List saved" flash message

  Scenario: Change marketing list type from 'Dynamic' to 'On demand'
    Given I fill form with:
      | Type | On demand |
    When I save form
    Then I should see "Marketing List saved" flash message
    And should not see "Invalid query"
