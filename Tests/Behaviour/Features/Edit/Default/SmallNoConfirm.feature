# Features/Edit/Default/SmallNoConfirm.feature
@Edit @EditDefault @EditDefaultSmallNoConfirm
Feature: SmallNoConfirm

  Scenario: Reset Frontend Sessions
    Given I am on "/index.php?id=38"
    Then I should see "All frontend sessions deleted"


  Scenario: Reset Frontend Users
    Given I am on "/index.php?id=39"
    Then I should see "FE Users reset successfully"


  @javascript
  Scenario: Login as frontend user and test profile update
    # Login
    Given I am on "/index.php?id=2"
    Then I should see "You are not yet logged in as frontend user"
    Then I fill in "Username" with "akellner"
    Then I fill in "Password" with "akellner"
    Then I press "Login"
    Then I wait "2" seconds
    Then I should see "You are logged in as frontend user"

    # Test
    Given I am on "/index.php?id=36"
    Then I should see "deutsch"
    Then I should not see "Please log in before"
    Then I should see "Firstname"
    Then I should see "Lastname"
    Then I should see "Email"
    And I press "Update Profile"
    And I wait "2" seconds

    Then I should see "No changes detected, nothing to update"
    And I fill in the following:
      | Firstname | [random] |
      | Lastname | [random] |
      | Email | [random]@in2code.de |
    And I press "Update Profile"
    And I wait "2" seconds

    Then I should see "Profile successfully changed"

    Given I am on "/index.php?id=33"
    Then I should see "[random:1]"
    Then I should see "[random:2]"
    Then I should see "[random:3]"


  Scenario: Reset Frontend Users
    Given I am on "/index.php?id=39"
    Then I should see "FE Users reset successfully"
