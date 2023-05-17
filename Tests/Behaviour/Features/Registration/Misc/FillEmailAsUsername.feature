# Features/Registration/Misc/FillEmailAsUsername.feature
@Registration @RegistrationMisc @FillEmailAsUsername
Feature: FillEmailAsUsername

  Scenario: Check if email field can be used as username
    Given I am on "/index.php?id=138"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Password | test |
      | Repeat Password | test |
      | Email | FillEmailAsUsername@local.de |
    And I press "Create Profile Now"
    Then I wait "4" seconds
    Then I should see "User registration"

    Given I am on "/index.php?id=89"
    Then I should see "FillEmailAsUsername@local.de"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
