<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class ResourceController
{
    public function __construct(
        private BaseResourceController $decoratedResourceController,
        private MetadataInterface $metadata,
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private RedirectHandlerInterface $redirectHandler,
    ) {
    }

    public function showAction(Request $request): Response
    {
        return $this->decoratedResourceController->showAction($request);
    }

    public function indexAction(Request $request): Response
    {
        return $this->decoratedResourceController->indexAction($request);
    }

    public function createAction(Request $request): Response
    {
        return $this->decoratedResourceController->createAction($request);
    }

    public function updateAction(Request $request): Response
    {
        return $this->decoratedResourceController->updateAction($request);
    }

    public function deleteAction(Request $request): Response
    {
        return $this->decoratedResourceController->deleteAction($request);
    }

    public function bulkDeleteAction(Request $request): Response
    {
        return $this->decoratedResourceController->bulkDeleteAction($request);
    }

    public function applyStateMachineTransitionAction(Request $request): Response
    {
        try {
            return $this->decoratedResourceController->applyStateMachineTransitionAction($request);
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
