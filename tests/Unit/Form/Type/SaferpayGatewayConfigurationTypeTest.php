<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Form\Type;

use CommerceWeavers\SyliusSaferpayPlugin\Form\Type\SaferpayGatewayConfigurationType;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;

final class SaferpayGatewayConfigurationTypeTest extends TestCase
{
    use ProphecyTrait;

    private SaferpayGatewayConfigurationType $formType;

    protected function setUp(): void
    {
        $this->formType = new SaferpayGatewayConfigurationType();
    }

    public function test_it_disables_password_field_mapping_if_no_passed_along_data(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $event = new PreSubmitEvent($form->reveal(), []);

        $form->remove('password')->shouldBeCalled()->willReturn($form);
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->formType->onPreSubmit($event);
    }

    public function test_it_disables_password_field_mapping_if_password_is_null(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $event = new PreSubmitEvent($form->reveal(), ['password' => null]);

        $form->remove('password')->shouldBeCalled()->willReturn($form);
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->formType->onPreSubmit($event);
    }

    public function test_it_disables_password_field_mapping_if_password_is_empty(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $event = new PreSubmitEvent($form->reveal(), ['password' => '']);

        $form->remove('password')->shouldBeCalled()->willReturn($form);
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $this->formType->onPreSubmit($event);
    }

    public function test_it_does_not_disable_password_field_mapping_if_password_is_not_empty(): void
    {
        $form = $this->prophesize(FormInterface::class);
        $event = new PreSubmitEvent($form->reveal(), ['password' => 'not empty']);

        $form->remove('password')->shouldNotBeCalled();
        $form
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password', 'mapped' => false])
            ->shouldNotBeCalled()
        ;

        $this->formType->onPreSubmit($event);
    }
}
