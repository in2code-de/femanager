# Features/Registration/Default/SmallNoConfirm.feature
@Registration @RegistrationDefault @RegistrationDefaultSmallNoConfirmLogin
Feature: SmallNoConfirm

  Scenario: Check if a small registration is possible
    Given I am on "/index.php?id=34"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"
    Then I wait "4" seconds

    Then I should see "User registration"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
