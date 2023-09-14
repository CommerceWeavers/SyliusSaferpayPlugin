<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use Exception;
use Payum\Core\Payum;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

final class AssertAction
{
    public function __construct(
        private Payum $payum,
        private GetStatusFactoryInterface $getStatusRequestFactory,
        private ResolveNextRouteFactoryInterface $resolveNextRouteRequestFactory,
        private AssertFactoryInterface $assertFactory,
        private RouterInterface $router,
    ) {
    }

    /** @throws Exception */
    public function __invoke(Request $request): RedirectResponse
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);
        $gateway = $this->payum->getGateway($token->getGatewayName());

        $assert = $this->assertFactory->createNewWithModel($token);
        $gateway->execute($assert);

        $status = $this->getStatusRequestFactory->createNewWithModel($assert->getFirstModel());
        $gateway->execute($status);

        $resolveNextRoute = $this->resolveNextRouteRequestFactory->createNewWithModel($assert->getFirstModel());
        $gateway->execute($resolveNextRoute);

        $this->payum->getHttpRequestVerifier()->invalidate($token);

        $routeName = $resolveNextRoute->getRouteName();
        if (null === $routeName) {
            throw new RouteNotFoundException('Route not found.');
        }

        $this->handleFlashMessage($status, $request);

        return new RedirectResponse($this->router->generate($routeName, $resolveNextRoute->getRouteParameters()));
    }

    private function handleFlashMessage(GetStatusInterface $status, Request $request): void
    {
        if ($status->isCanceled()) {
            $this->addFlashMessage($request, 'info', 'sylius.payment.cancelled');

            return;
        }

        if ($status->isFailed()) {
            $this->addFlashMessage($request, 'error', 'sylius.payment.failed');
        }
    }

    private function addFlashMessage(Request $request, string $type, string $message): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add($type, $message);
    }
}
