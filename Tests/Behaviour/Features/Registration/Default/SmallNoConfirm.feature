# Features/Registration/Default/SmallNoConfirm.feature
@Registration @RegistrationDefault @RegistrationDefaultSmallNoConfirm
Feature: SmallNoConfirm

  Scenario: Check if a small registration is possible
    Given I am on "/index.php?id=4"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"

    Then I should see "User registration"

    Given I am on "/index.php?id=33"
    Then I should see "[random:1]"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
