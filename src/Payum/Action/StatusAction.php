<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusAction implements ActionInterface
{
    public const STATUS_NEW = 'NEW';

    public const STATUS_AUTHORIZED = 'AUTHORIZED';

    public const STATUS_CAPTURED = 'CAPTURED';

    /** @param GetStatusInterface $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();
        $paymentDetails = $payment->getDetails();

        if (self::STATUS_NEW === $paymentDetails['status']) {
            $request->markNew();

            return;
        }

        if (self::STATUS_AUTHORIZED === $paymentDetails['status']) {
            $request->markAuthorized();

            return;
        }

        if (self::STATUS_CAPTURED === $paymentDetails['status']) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatus && $request->getFirstModel() instanceof SyliusPaymentInterface;
    }
}
