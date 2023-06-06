<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use Payum\Core\Payum;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class WebhookAction
{
    public function __construct(
        private Payum $payum,
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $this->commandBus->dispatch(new AssertPaymentCommand($token->getHash()));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
