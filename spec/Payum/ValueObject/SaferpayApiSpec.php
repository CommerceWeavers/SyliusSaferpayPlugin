<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\ValueObject;

use PhpSpec\ObjectBehavior;

final class SaferpayApiSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith('username', 'password', 'customer_id', 'terminal_id');
    }

    function it_returns_a_username(): void
    {
        $this->getUsername()->shouldReturn('username');
    }

    function it_returns_a_password(): void
    {
        $this->getPassword()->shouldReturn('password');
    }

    function it_returns_a_customer_id(): void
    {
        $this->getCustomerId()->shouldReturn('customer_id');
    }

    function it_returns_a_terminal_id(): void
    {
        $this->getTerminalId()->shouldReturn('terminal_id');
    }
}
