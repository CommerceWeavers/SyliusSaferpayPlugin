<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Provider;

final class SaferpayPaymentMethodsProvider implements SaferpayPaymentMethodsProviderInterface
{
    public function provide(): array
    {
        return [
            'Account to Account' => 'ACCOUNTTOACCOUNT',
            'Alipay' => 'ALIPAY',
            'American Express' => 'AMEX',
            'Bancontact' => 'BANCONTACT',
            'Bonus' => 'BONUS',
            'Diners Club' => 'DINERS',
            'Direct Debit' => 'DIRECTDEBIT',
            'ePrzelewy' => 'EPRZELEWY',
            'EPS' => 'EPS',
            'Giropay' => 'GIROPAY',
            'iDEAL' => 'IDEAL',
            'Invoice' => 'INVOICE',
            'JCB' => 'JCB',
            'Klarna' => 'KLARNA',
            'Maestro' => 'MAESTRO',
            'Mastercard' => 'MASTERCARD',
            'MyOne' => 'MYONE',
            'Payconiq' => 'PAYCONIQ',
            'Paydirekt' => 'PAYDIREKT',
            'PayPal' => 'PAYPAL',
            'Postcard' => 'POSTCARD',
            'PostFinance' => 'POSTFINANCE',
            'SOFORT' => 'SOFORT',
            'TWINT' => 'TWINT',
            'UnionPay' => 'UNIONPAY',
            'Visa' => 'VISA',
            'WLCryptoPayments' => 'WLCRYPTOPAYMENTS',
        ];
    }
}
