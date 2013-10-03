Feature: Some site pages show stuff
    Scenario: Front page shows expected
        Given anonymeus user
        When "/hello/wut" page is loaded
        Then page has "Days without"