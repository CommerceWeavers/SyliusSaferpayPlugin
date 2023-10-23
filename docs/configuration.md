## Configuration

To configure the Saferpay payment method, you need to register on the Saferpay Worldline website.
Let's start with the test environment - it can be done [here](https://test.saferpay.com/BO/SignUp).

After you successfully create an account and log in, you can move forward to its configuration:

1. Create the API credentials

![Create API](./img/1-create-api.png)

2. Specify its password (and remember it! it will be used later)

![API password](./img/2-password.png)

3. Save Customer ID and API username (you will need them later as well)

![Customer ID and Terminal ID](./img/3-customer-id-api-username.png)

4. Take a look at the Terminal and its ID (also needed for the further configuration)

![Terminal ID](./img/4-terminal-id.png)

5. Now you can configure Saferpay payment method in Sylius

![Sylius payment method configuration](./img/5-sylius-pm-creation.png)

6. Fill in the form with the data you've got from the Saferpay panel

![Sylius payment method configuration](./img/6-pm-configuration.png)

7. Beware! By default, after payment method's creation there are no payment methods enabled for Saferpay. To configure them
go to the `/admin/payment-methods/{id}/configure-saferpay-payment-methods` URL and check out which payment methods you would
like to use.

![Payment methods configuration](./img/overview/payment-methods-configuration.png)

Done! You're now ready to use Saferpay payment method in your webshop 🎉

---

Prev: [Installation](installation.md)
Next: [Development](development.md)
