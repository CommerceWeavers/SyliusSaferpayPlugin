<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class PaymentController extends ResourceController
{
    public function applyStateMachineTransitionAction(Request $request): Response
    {
        try {
            return parent::applyStateMachineTransitionAction($request);
        } catch (PaymentRefundFailedException) {
            $this->addFlashMessage($request, 'error', 'sylius_saferpay.payment.refund_failed');

            $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

            return $this->redirectHandler->redirectToReferer($configuration);
        }
    }

    private function addFlashMessage(Request $request, string $type, string $message): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add($type, $message);
    }
}
