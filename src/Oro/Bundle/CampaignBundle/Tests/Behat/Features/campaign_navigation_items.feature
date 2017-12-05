@regression
@ticket-CRM-8143
@fixture-OroCampaignBundle:CampaignFixture.yml

Feature: Campaign Navigation Items
  In order to manage Campaign feature
  as an Administrator
  I should be able to see or not see navigation items based on feature state

  Scenario: Pin and Add to favorites Campaign Grid page
    Given I login as administrator
    And I should see Marketing/Campaigns in main menu
    And I go to Marketing/Campaigns
    And I should see "Campaign 1" in grid
    When I pin page
    And add page to favorites
    Then Campaigns link must be in pin holder
    And Favorites must contain "Campaigns - Marketing"

  Scenario: Pin and Add to favorites Campaign View page
    Given I click View Campaign 1 in grid
    When I pin page
    And add page to favorites
    Then Campaign 1 link must be in pin holder
    And Favorites must contain "Campaign 1 - Campaigns - Marketing"

  Scenario: Disable feature and validate links
    Given I go to Dashboards/Dashboard
    When I disable Campaign feature
    And I reload the page
    Then Campaigns link must not be in pin holder
    And Favorites must not contain "Campaigns - Marketing"
    And Campaign 1 link must not be in pin holder
    And Favorites must not contain "Campaign 1 - Campaigns - Marketing"

  Scenario: Re-Enable feature and validate links
    Given I go to Dashboards/Dashboard
    When I enable Campaign feature
    And I reload the page
    Then Campaigns link must be in pin holder
    And Favorites must contain "Campaigns - Marketing"
    And Campaign 1 link must be in pin holder
    And Favorites must contain "Campaign 1 - Campaigns - Marketing"
