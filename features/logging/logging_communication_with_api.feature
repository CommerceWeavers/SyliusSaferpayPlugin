@logging
Feature: Logging communication with API
    In order to make debugging easier
    As a Developer
    I want to be able to browse logs with communication with API

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@example.com" identified by "password123"
        And the store has a payment method "Saferpay" with a code "SAFERPAY" and Saferpay gateway
        And the store has a product "CommerceWeavers T-Shirt" priced at "$29.99"
        And the store ships everywhere for Free
        And I am logged in as "john@example.com"

    @ui @todo
    Scenario: Logging communication with API
        Given I added product "CommerceWeavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        When I finalize successfully the payment on the Saferpay's page
        Then I should see a transaction log with type "", status "" and description ""
        And I should see another transaction log with type "", status "" and description ""
        And I should see another transaction log with type "", status "" and description ""
