# Features/Registration/Misc/NoPid.feature
@Registration @RegistrationMisc @RegistrationMiscNoPid
Feature: NoTyposcript

  Scenario: Check if there is a message if admin forgot to set the start point in plugin
    Given I am on "/index.php?id=24"
    Then I should see "No Storage PID was set. Please set the storage PID via TypoScript or in the plugin settings."
