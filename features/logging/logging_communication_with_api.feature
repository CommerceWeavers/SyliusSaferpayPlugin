@logging
Feature: Logging communication with API
    In order to ensure that communication with the API is working correctly
    As an Administrator
    I want to be able to browse logs of the communication with the API

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "PHP T-Shirt"
        And the store ships everywhere for Free
        And the store allows paying with "Saferpay"
        And there is a customer "john.doe@gmail.com" that placed an order "#00000022"
        And the customer bought a single "PHP T-Shirt"
        And the customer chose "Free" shipping method to "United States" with "Saferpay" payment
        And this order has been paid successfully with Saferpay payment method
        And I am logged in as an administrator

    @ui
    Scenario: Logging of communication with the Saferpay
        When I check the Saferpay's transaction logs
        Then I should see the successful transaction log for order "#00000022", described as "Payment authorization"
        And I should see the successful transaction log for order "#00000022", described as "Payment assertion"
        And I should see the successful transaction log for order "#00000022", described as "Payment capture"
