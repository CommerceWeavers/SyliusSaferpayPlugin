<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payment\Command\AssertPaymentCommand;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessor;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\Payum;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class WebhookAction
{
    public function __construct(
        private Payum $payum,
        private MessageBusInterface $commandBus,
        private LoggerInterface $logger,
        private PaymentProviderInterface $paymentProvider,
        private SaferpayPaymentProcessor $saferpayPaymentProcessor,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->logger->debug('Handling webhook started');

        $orderToken = $request->attributes->get('order_token');
        $payment = $this->paymentProvider->provideForOrder($orderToken);

        try {
            $this->saferpayPaymentProcessor->lock($payment);
        } catch (PaymentAlreadyProcessedException) {
            $this->logger->debug('Webhook aborted - payment already processed');

            return new JsonResponse(status: Response::HTTP_OK);
        }

        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        try {
            $this->commandBus->dispatch(new AssertPaymentCommand($token->getHash()));
        } catch (HandlerFailedException $exception) {
            $this->logger->debug('Webhook failed: ', ['exception' => $exception->getMessage()]);

            return new JsonResponse(status: Response::HTTP_BAD_REQUEST);
        }

        $this->logger->debug('Webhook handled successfully');

        return new JsonResponse(status: Response::HTTP_OK);
    }
}
