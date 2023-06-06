@paying_with_saferpay
Feature: Failing to complete a payment using Saferpay payment method
    In order to pay for my order after a payment problem has occurred
    As a Customer
    I want to be able to choose a payment method again

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@example.com"
        And the store allows paying with Saferpay gateway
        And the store has a product "Commerce Weavers T-Shirt" priced at "$29.99"
        And the store ships everywhere for Free
        And I am logged in as "john@example.com"

    @ui
    Scenario: Failing to complete a payment using Saferpay payment method
        Given I added product "Commerce Weavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        When I fail to complete the payment on the Saferpay's page
        Then I should be notified that my payment has failed
        And I should be able to pay again

    @ui
    Scenario: Completing a payment using Saferpay payment method after a payment failure
        Given I added product "Commerce Weavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        And I have failed to complete the payment on the Saferpay's page
        When I successfully pay again on the Saferpay's page
        Then I should be notified that my payment has been completed
        And I should see the thank you page

    @ui
    Scenario: Retrying to complete a payment using Saferpay payment method
        Given I added product "Commerce Weavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        And I have failed to complete the payment on the Saferpay's page
        When I fail again to complete the payment on the Saferpay's page
        Then I should be notified that my payment has failed
        And I should be able to pay again
