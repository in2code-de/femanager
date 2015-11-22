# Features/Registration/Misc/NoTyposcript.feature
@Registration @RegistrationMisc @RegistrationMiscNoTyposcript
Feature: NoTyposcript

  Scenario: Check if there is a message if admin forgot to include the static template
    Given I am on "/index.php?id=23"
    Then I should see "No TypoScript found. Did you already include the static Template?"
