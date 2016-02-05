# Features/Edit/Default/NotLoggedIn.feature
@Edit @EditDefault @EditDefaultNotLoggedIn
Feature: NotLoggedIn

  Scenario: Check edit is not available if not logged in
    Given I am on "/index.php?id=36"
    Then I should see "Please log in before"
    Then I should not see "Firstname"
    Then I should not see "Lastname"
    Then I should not see "Email"
