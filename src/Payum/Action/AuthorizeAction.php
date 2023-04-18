<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Payum\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Client\SaferpayClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class AuthorizeAction implements ActionInterface
{
    public function __construct(
        private SaferpayClientInterface $saferpayClient,
    ) {
    }

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, Authorize::class);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $token = $request->getToken();
        Assert::notNull($token);

        $response = $this->saferpayClient->authorize($payment, $token);

        $token->setAfterUrl($response->getRedirectUrl());

        $payment->setDetails([
            'request_id' => $response->getResponseHeader()->getRequestId(),
            'saferpay_token' => $response->getToken(),
            'status' => StatusAction::STATUS_NEW,
        ]);
    }

    public function supports($request): bool
    {
        return ($request instanceof Authorize) && ($request->getModel() instanceof PaymentInterface);
    }
}
