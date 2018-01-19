# Features/Registration/Validation/Terms.feature
@Registration @RegistrationValidation @RegistrationValidationTerms
Feature: Terms

  Scenario: Check if validation for terms work as expected for serverside validation
    Given I am on "/index.php?id=84"
    And I fill in the following:
      | Username | [random] |
      | Password | testtest |
      | Repeat Password | testtest |
      | Email | info@in2code.ws |
    And I press "Create Profile Now"

    Then I should see "Terms: Field I accept the terms and conditions is required"

  @javascript
  Scenario: Check if validation for terms work as expected for clientside validation
    Given I am on "/index.php?id=86"
    And I fill in the following:
      | Username | [random] |
      | Password | testtest |
      | Repeat Password | testtest |
      | Email | info@in2code.ws |
    And I press "Create Profile Now"

    Then I wait "2" seconds
    Then I should see "Field I accept the terms and conditions is required"
