# Features/Ratelimiter/LimitNewUserCreation
@Ratelimiter
Feature: RateLimitNewUserCreation

  # Clean up before
  Scenario: Reset users and cache
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"

    Given I am on "/index.php?id=132"
    Then I should see "Rate limiter cache has been reset"

  Scenario: Check if registration for single user is possible
    Given I am on "/index.php?id=4"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | firstuser |
      | Password | test |
      | Repeat Password | test |
      | Email | firstuser@test.de |
    And I press "Create Profile Now"

    Then I should see "User registration"

  # prerequisite: limit of rate limiter is set to 3
  Scenario: Check if registration of multiple users fails because of rate limiter
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"

    Given I am on "/index.php?id=132"
    Then I should see "Rate limiter cache has been reset"

    Given I am on "/index.php?id=4"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | seconduser |
      | Password | test |
      | Repeat Password | test |
      | Email | seconduser@test.de |
    And I press "Create Profile Now"

    Then I should see "User registration"

    Given I am on "/index.php?id=4"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | thirduser |
      | Password | test |
      | Repeat Password | test |
      | Email | thirduser@test.de |
    And I press "Create Profile Now"

    Given I am on "/index.php?id=4"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | fourthuser |
      | Password | test |
      | Repeat Password | test |
      | Email | fourthuser@test.de |
    And I press "Create Profile Now"

    Given I am on "/index.php?id=4"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | fifthuser |
      | Password | test |
      | Repeat Password | test |
      | Email | fifthuser@test.de |
    And I press "Create Profile Now"

    Then I should see "Too many attempts"

# Clean up after
  Scenario: Reset users and cache
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"

    Given I am on "/index.php?id=39"
    Then I should see "FE Users reset successfully"

    Given I am on "/index.php?id=132"
    Then I should see "Rate limiter cache has been reset"
