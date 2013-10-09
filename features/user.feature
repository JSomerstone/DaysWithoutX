Feature: Some site pages show stuff

    Scenario: Front page shows expected
        Given "/" page is loaded
        When "/" page is loaded
        Then page has "Days without"

    Scenario: User creates new counter
        Given "/" page is loaded
        When user posts new counter "Smoking"
        Then page has "Days without Smoking"

    Scenario: User views existing counter
        Given "public" counter "Foobar" with "19" days exists
        When "/foobar" page is loaded
        Then the page exists
        And page has "19"
        And page has "Foobar"

    Scenario: User resets existing counter
        Given "public" counter "Resetme" with "1" days exists
        When user resets counter "Resetme"
        Then page has "0"
        And page has "Resetme"