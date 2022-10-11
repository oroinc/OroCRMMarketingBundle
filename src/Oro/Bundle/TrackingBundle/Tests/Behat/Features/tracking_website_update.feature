@ticket-BAP-21510
Feature: Tracking website update
  In order to check updating website tracking data
  As an Administrator
  I need to create and update the tracking website

  Scenario: Create website tracking
    Given login as administrator
    When go to Marketing/ Tracking Websites
    And I click "Create Tracking Website"
    And I fill form with:
      | Owner      | John Doe           |
      | Name       | name               |
      | Identifier | unique             |
      | Url        | http://example.com |
    And I save and close form
    Then I should see "Tracking Website saved" flash message
    And I should see "_paq.push(['setSiteId', 'unique'])"

  Scenario: View and filter website track in grid
    When go to Marketing/ Tracking Websites
    And filter Identifier as is equal to "unique"
    Then there is one record in grid
    And I should see following grid containing rows:
      | Name | Identifier | Url                | Owner    |
      | name | unique     | http://example.com | John Doe |

  Scenario: Update website tracking
    When I click edit unique in grid
    And I fill form with:
      | Name       | nameUP             |
      | Identifier | unique2            |
      | Url        | http://example.org |
    And I save and close form
    Then I should see "Tracking Website saved" flash message
    And I should see "_paq.push(['setSiteId', 'unique2'])"
