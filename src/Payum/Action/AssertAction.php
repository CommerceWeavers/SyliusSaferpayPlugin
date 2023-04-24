<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Webmozart\Assert\Assert as WebmozartAssert;

final class AssertAction implements ActionInterface
{
    public function __construct(
        private SaferpayClientInterface $saferpayClient,
        private RequestStack $requestStack,
    ) {
    }

    /** @param Assert $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $response = $this->saferpayClient->assert($payment);

        $paymentDetails = $payment->getDetails();
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $paymentDetails['status'] = StatusAction::STATUS_FAILED;

            $payment->setDetails($paymentDetails);

            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('error', 'sylius.payment.failed');

            return;
        }

        $responseTransaction = $response['Transaction'];
        WebmozartAssert::isArray($responseTransaction);

        $paymentDetails['status'] = $response->getTransaction()->getStatus();
        $paymentDetails['transaction_id'] = $response->getTransaction()->getId();
        $payment->setDetails($paymentDetails);
    }

    public function supports($request): bool
    {
        return ($request instanceof Assert) && ($request->getModel() instanceof PaymentInterface);
    }
}
