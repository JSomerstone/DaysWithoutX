Feature: User can create & reset password protected counters
    In order to create password protected counters
    The user must provide nick & password
    So that created counter can be protected

    Scenario: Front page shows expected
        Given "/" page is loaded
        When "/" page is loaded
        Then page has "Nick"
            And page has button "Private"
