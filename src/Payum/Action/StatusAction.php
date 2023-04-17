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

    /** @param GetStatus $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentStatus = $this->getPaymentStatus($request);

        if ($this->isNew($paymentStatus)) {
            $request->markNew();

            return;
        }

        if ($this->isAuthorized($paymentStatus)) {
            $request->markAuthorized();

            return;
        }

        if ($this->isCaptured($paymentStatus)) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
    }

    private function getPaymentStatus(GetStatusInterface $request): string
    {
        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getFirstModel();
        /** @var array{status: string} $paymentDetails */
        $paymentDetails = $payment->getDetails();

        return  $paymentDetails['status'];
    }

    private function isNew($status): bool
    {
        return self::STATUS_NEW === $status;
    }

    private function isAuthorized($status): bool
    {
        return self::STATUS_AUTHORIZED === $status;
    }

    private function isCaptured($status): bool
    {
        return self::STATUS_CAPTURED === $status;
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatus && $request->getFirstModel() instanceof SyliusPaymentInterface;
    }
}
