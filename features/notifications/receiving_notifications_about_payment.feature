@receiving_notifications
Feature: Receiving notifications from Saferpay
    In order to process payment when a customer does not return to the shop after payment
    As a Shop Owner
    I want to have notifications from Saferpay handled

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@example.com"
        And the store allows paying with Saferpay
        And the store has a product "Commerce Weavers T-Shirt" priced at "$29.99"
        And the store ships everywhere for free
        And I am logged in as "john@example.com"

    @webhook
    Scenario: A notification from Saferpay is received after the payment is finalized
        Given I placed an order with using Saferpay
        And I paid for the order successfully
        And I returned to the store
        When the system receives a notification about payment status
        Then there should be only one payment for this order
        And the payment should be completed

    @webhook
    Scenario: A notification from Saferpay is received before the payment is finalized
        Given I placed an order with using Saferpay
        And I paid for the order successfully
        But I did not return to the store
        When the system receives a notification about payment status
        Then there should be only one payment for this order
        And the payment should be completed

    @webhook
    Scenario: A customer returns to the store after payment but a notification from Saferpay is already received
        Given I placed an order with using Saferpay
        And I paid for the order successfully
        But before I returned to the store, the system received a notification about payment status
        When I return to the store
        Then I should see the thank you page
        And there should be only one payment for this order
        And the payment should be completed
