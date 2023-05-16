# Features/Registration/Validation/Terms.feature
@Registration @RegistrationValidation @RegistrationCountries
Feature: Countries

  Scenario: Check if validation for Couniries work as expected for serverside validation
    Given I am on "/index.php?id=131"
    And I fill in the following:
      | Username | [random] |
      | Password | testtest |
      | Repeat Password | testtest |
      | Email | info@in2code.ws |
    And I press "Create Profile Now"

    Then I wait "4" seconds
    Then I should see "Country: Field Country is required"

  Scenario: Check if validation for Countries are stored after unsucessful submit
    Given I am on "/index.php?id=131"
    And I fill in the following:
      | Username | [random] |
      | Password | testtest |
      | Repeat Password | testtest |
      | Country | ATA |
    And I press "Create Profile Now"

    Then I wait "4" seconds
    Then I should see "Email: Field Email is required"
    Then I should see "Antarctica"

  @javascript @RegistrationCountries1
  Scenario: Check if validation for Countries work as expected for clientside validation
    Given I am on "/index.php?id=130"
    And I fill in the following:
      | Username | [random] |
      | Password | testtest |
      | Repeat Password | testtest |
      | Email | info@in2code.ws |
    And I press "Create Profile Now"

    Then I wait "6" seconds
    And I fill in the following:
      | Email | info@in2code.ws |
    Then I should see "Field Country is required"

  # @TODO: Reactivate when static_info_tables is avaiable for TYPO3v12
#  @javascript @Fail
#  Scenario: Check if validation for State are stored after unsucessful submit
#    Given I am on "/index.php?id=130"
#    And I fill in the following:
#      | Username | [random] |
#      | Password | testtest |
#      | Repeat Password | testtest |
#      | Country | AUT |
#
#    Then I wait "4" seconds
#    And I fill in the following:
#      | State | 5 |
#
#    And I fill in the following:
#      | Email | info@in2code.ws |
#
#    And I press "Create Profile Now"
#
#    Then I wait "4" seconds
#    Then I should see "User registration"
