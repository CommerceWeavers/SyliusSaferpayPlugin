<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert as WebmozartAssert;

final class AssertAction implements ActionInterface
{
    public function __construct(
        private SaferpayClientInterface $saferpayClient,
    ) {
    }

    /**
     * @param Assert $request
     *
     * @psalm-suppress MissingReturnType
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $response = $this->saferpayClient->assert($payment);
        $responseTransaction = $response['Transaction'];
        WebmozartAssert::isArray($responseTransaction);

        $paymentDetails = $payment->getDetails();
        $paymentDetails['status'] = (string) $responseTransaction['Status'];
        $paymentDetails['transaction_id'] = (string) $responseTransaction['Id'];

        $payment->setDetails($paymentDetails);
    }

    public function supports($request): bool
    {
        return ($request instanceof Assert) && ($request->getModel() instanceof PaymentInterface);
    }
}
