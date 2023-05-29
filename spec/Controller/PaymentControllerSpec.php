<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ResourceBundle\Controller\AuthorizationCheckerInterface;
use Sylius\Bundle\ResourceBundle\Controller\EventDispatcherInterface;
use Sylius\Bundle\ResourceBundle\Controller\FlashHelperInterface;
use Sylius\Bundle\ResourceBundle\Controller\NewResourceFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceDeleteHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceFormFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceUpdateHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\SingleResourceProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\StateMachineInterface;
use Sylius\Bundle\ResourceBundle\Controller\ViewHandlerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class PaymentControllerSpec extends ObjectBehavior
{
    function let(
        MetadataInterface $metadata,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        ViewHandlerInterface $viewHandler,
        RepositoryInterface $repository,
        FactoryInterface $factory,
        NewResourceFactoryInterface $newResourceFactory,
        ObjectManager $manager,
        SingleResourceProviderInterface $singleResourceProvider,
        ResourcesCollectionProviderInterface $resourcesFinder,
        ResourceFormFactoryInterface $resourceFormFactory,
        RedirectHandlerInterface $redirectHandler,
        FlashHelperInterface $flashHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        StateMachineInterface $stateMachine,
        ResourceUpdateHandlerInterface $resourceUpdateHandler,
        ResourceDeleteHandlerInterface $resourceDeleteHandler,
        ContainerInterface $container,
    ): void {
        $this->beConstructedWith(
            $metadata,
            $requestConfigurationFactory,
            $viewHandler,
            $repository,
            $factory,
            $newResourceFactory,
            $manager,
            $singleResourceProvider,
            $resourcesFinder,
            $resourceFormFactory,
            $redirectHandler,
            $flashHelper,
            $authorizationChecker,
            $eventDispatcher,
            $stateMachine,
            $resourceUpdateHandler,
            $resourceDeleteHandler
        );

        $this->setContainer($container);
    }
    function it_handles_payment_refund_failed_exception_during_applying_state_machine_transition(
        MetadataInterface $metadata,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        RepositoryInterface $repository,
        ObjectManager $manager,
        SingleResourceProviderInterface $singleResourceProvider,
        RedirectHandlerInterface $redirectHandler,
        FlashHelperInterface $flashHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        CsrfTokenManagerInterface $csrfTokenManager,
        ContainerInterface $container,
        StateMachineInterface $stateMachine,
        ResourceUpdateHandlerInterface $resourceUpdateHandler,
        RequestConfiguration $configuration,
        ResourceInterface $resource,
        ResourceControllerEvent $event,
        Request $request,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $metadata->getApplicationName()->willReturn('sylius');
        $metadata->getName()->willReturn('product');

        $requestConfigurationFactory->create($metadata, $request)->willReturn($configuration);
        $configuration->hasPermission()->willReturn(true);
        $configuration->getPermission(ResourceActions::UPDATE)->willReturn('sylius.product.update');
        $configuration->isCsrfProtectionEnabled()->willReturn(true);
        $request->get('_csrf_token')->willReturn('xyz');

        $container->has('security.csrf.token_manager')->willReturn(true);
        $container->get('security.csrf.token_manager')->willReturn($csrfTokenManager);
        $csrfTokenManager->isTokenValid(new CsrfToken('1', 'xyz'))->willReturn(true);

        $authorizationChecker->isGranted($configuration, 'sylius.product.update')->willReturn(true);
        $singleResourceProvider->get($configuration, $repository)->willReturn($resource);

        $resource->getId()->willReturn('1');

        $configuration->isHtmlRequest()->willReturn(true);

        $eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource)->willReturn($event);
        $event->isStopped()->willReturn(false);

        $stateMachine->can($configuration, $resource)->willReturn(true);
        $resourceUpdateHandler->handle($resource, $configuration, $manager)->willThrow(PaymentRefundFailedException::class);

        $flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource)->shouldNotBeCalled();
        $eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource)->shouldNotBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius_saferpay.payment.refund_failed')->shouldBeCalled();

        $requestConfigurationFactory->create($metadata, $request)->willReturn($configuration);
        $redirectHandler->redirectToReferer($configuration)->shouldBeCalled();

        $this->applyStateMachineTransitionAction($request)->shouldHaveType(Response::class);
    }
}
