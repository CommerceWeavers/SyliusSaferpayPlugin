<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;

final class SaferpayGatewayConfigurationTypeSpec extends ObjectBehavior
{
    function it_should_disable_password_field_mapping_if_no_passed_along_data(FormInterface $form): void
    {
        $event = new PreSubmitEvent($form->getWrappedObject(), []);

        $form->remove('password')->willReturn($form)->shouldBeCalled();
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->willReturn($form)
            ->shouldBeCalled()
        ;

        $this->onPreSubmit($event);
    }

    function it_should_disable_password_field_mapping_if_password_is_null(FormInterface $form): void
    {
        $event = new PreSubmitEvent($form->getWrappedObject(), ['password' => null]);

        $form->remove('password')->willReturn($form)->shouldBeCalled();
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->willReturn($form)
            ->shouldBeCalled()
        ;

        $this->onPreSubmit($event);
    }

    function it_should_disable_password_field_mapping_if_password_is_empty(FormInterface $form): void
    {
        $event = new PreSubmitEvent($form->getWrappedObject(), ['password' => '']);

        $form->remove('password')->willReturn($form)->shouldBeCalled();
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->willReturn($form)
            ->shouldBeCalled()
        ;

        $this->onPreSubmit($event);
    }

    function it_should_not_disable_password_field_mapping_if_password_is_not_empty(FormInterface $form): void
    {
        $event = new PreSubmitEvent($form->getWrappedObject(), ['password' => 'not empty']);

        $form->remove('password')->willReturn($form)->shouldNotBeCalled();
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->willReturn($form)
            ->shouldNotBeCalled()
        ;

        $this->onPreSubmit($event);
    }
}
