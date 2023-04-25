@logging
Feature: Logging communication with API
    In order to make debugging easier
    As a Developer
    I want to be able to browse logs with communication with API

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "PHP T-Shirt"
        And the store ships everywhere for Free
        And the store allows paying with "Saferpay"
        And there is a customer "john.doe@gmail.com" that placed an order "#00000022"
        And the customer bought a single "PHP T-Shirt"
        And the customer chose "Free" shipping method to "United States" with "Saferpay" payment
        And the system has been notified about payment on this order
        And I am logged in as an administrator

    @ui
    Scenario: Logging communication with API
        When I check the Saferpay's Transaction Logs
        Then I should see a single transaction log with type "success" and description "Payment authorization" for order "#00000022"
        And I should see a single transaction log with type "success" and description "Payment assertion" for order "#00000022"
        And I should see another transaction log with type "success" and description "Payment capture" for order "#00000022"
