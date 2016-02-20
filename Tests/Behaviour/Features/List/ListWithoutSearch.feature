# Features/List/ListWithoutSearch.feature
@List @ListListWithoutSearch
Feature: ListWithoutSearch

  Scenario: Check if search is hidden
    Given I am on "/index.php?id=44"
    Then the sourcecode should not contain 'name="tx_femanager_pi1[filter][searchword]"'
