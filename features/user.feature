Feature: User can create & reset password protected counters
    In order to create password protected counters
    User must provide nick & password
    So that created counter can be protected

    Scenario: Front page shows expected
        Given "/" page is loaded
        When "/" page is loaded
        Then page has "Nick"
            And page has button "Private"

    Scenario: User creates private counter
        Given user "Mee" with password "fuubar123"
          And "/" page is loaded
        When "Mee" posts private counter "being sober" with password "foobar123"
        Then user is redirected to "/being-sober/mee"

    Scenario: User opens private counter
        Given user "Mee" with password "fuubar123"
         And "Mee" counter "Foobar" with "19" days exists
        When "/foobar/mee" page is loaded
        And the counter is "19"
        And page has "Days without Foobar"
