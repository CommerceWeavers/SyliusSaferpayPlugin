<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Factory\AssertFactoryInterface;
use Exception;
use Payum\Core\Payum;
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

        if ($status->isFailed()) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->getFlashBag()->add('error', 'sylius.payment.failed');
        }

        return new RedirectResponse($this->router->generate($routeName, $resolveNextRoute->getRouteParameters()));
    }
}
