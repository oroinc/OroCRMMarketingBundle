@ticket-BAP-21510
Feature: Campaign update
  In order to manage Campaign feature
  As an administrator
  I need to be able to create and edit Campaign

  Scenario: Campaign Create
    Given I login as administrator
    When I go to Marketing/Campaigns
    And I click "Create Campaign"
    And I fill form with:
      | Name        | new name         |
      | Code        | code-1234        |
      | Description | some description |
      | Budget ($)  | 154.54           |
    And I save and close form
    Then I should see "Campaign saved" flash message

  Scenario: Campaign Update
    When I click "Edit Campaign"
    Then I fill form with:
      | Code       | updated-code-1234 |
      | Budget ($) | 177               |
    And I save and close form
    Then I should see "Campaign saved" flash message

    When I go to Marketing/Campaigns
    Then I should see following grid containing rows:
      | Name     | Code              | Budget  |
      | new name | updated-code-1234 | $177.00 |
