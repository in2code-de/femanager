# Features/List/ListWithSearch.feature
@List @ListListWithSearch
Feature: ListWithSearch

  Scenario: Check if search is hidden
    Given I am on "/index.php?id=45"
    Then the sourcecode should contain 'name="tx_femanager_pi1[filter][searchword]"'
