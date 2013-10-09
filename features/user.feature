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
        When "Mee" posts private counter "being sober" with password "foobar123"
        Then page has "Days without being sober:"
            And the counter is "0"
            And page has link "Mee"
