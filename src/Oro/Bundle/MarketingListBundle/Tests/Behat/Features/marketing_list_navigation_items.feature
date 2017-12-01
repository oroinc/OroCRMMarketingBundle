@regression
@ticket-CRM-8143

Feature: Marketing List Navigation Items
  In order to manage Marketing List feature
  as an Administrator
  I should be able to see or not see navigation items based on feature state

  Scenario: Pin and Add to favorites Marketing List Grid page
    Given I login as administrator
    And I should see Marketing/Marketing Lists in main menu
    And I load Marketing List fixture
    And I go to Marketing/Marketing Lists
    And I should see "Dynamic Marketing List" in grid
    When I pin page
    And add page to favorites
    Then Marketing Lists link must be in pin holder
    And Favorites must contain "Marketing Lists - Marketing"

  Scenario: Pin and Add to favorites Marketing List View page
    Given I click View Dynamic Marketing List in grid
    When pin page
    And add page to favorites
    Then Dynamic Marketing List link must be in pin holder
    And Favorites must contain "Dynamic Marketing List - Marketing Lists - Marketing"

  Scenario: Disable feature and validate links
    Given I go to Dashboards/Dashboard
    When I disable Marketing List feature
    And I reload the page
    Then Marketing Lists link must not be in pin holder
    And Favorites must not contain "Marketing Lists - Marketing"
    And Dynamic Marketing List link must not be in pin holder
    And Favorites must not contain "Dynamic Marketing List - Marketing Lists - Marketing"

  Scenario: Re-Enable feature and validate links
    Given I go to Dashboards/Dashboard
    When I enable Marketing List feature
    And I reload the page
    Then Marketing Lists link must be in pin holder
    And Favorites must contain "Marketing Lists - Marketing"
    And Dynamic Marketing List link must be in pin holder
    And Favorites must contain "Dynamic Marketing List - Marketing Lists - Marketing"
