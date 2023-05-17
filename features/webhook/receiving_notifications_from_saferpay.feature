@receiving_notifications
Feature: Receiving notifications from Saferpay
    In order to process payment when a customer does not return to the shop after payment
    As a shop owner
    I want to have a mechanism to run a payment process when a notification is received from Saferpay

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@example.com"
        And the store has a payment method "Saferpay" with a code "SAFERPAY" and Saferpay gateway
        And the store has a product "Commerce Weavers T-Shirt" priced at "$29.99"
        And the store ships everywhere for Free
        And I am logged in as "john@example.com"

    @ui @todo
    Scenario: Completing a payment process before the system receive a notification from Saferpay
        Given I added product "Commerce Weavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        When I finalize successfully the payment on the Saferpay's page
        Then I should be redirected back to the store
        And I should see a successful payment message
        And the system should receive a notification from Saferpay

    @ui @todo
    Scenario: Completing a payment process after the system receive a notification from Saferpay
        Given I added product "Commerce Weavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        When I finalize successfully the payment on the Saferpay's page
        And the system receive a notification from Saferpay
        Then I should be redirected back to the store
        And I should see a successful payment message
