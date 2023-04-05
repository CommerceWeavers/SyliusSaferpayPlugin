<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Payum\ValueObject;

use PhpSpec\ObjectBehavior;

final class SaferpayApiSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith('username', 'password');
    }

    function it_gets_a_username(): void
    {
        $this->getUsername()->shouldReturn('username');
    }

    function it_gets_a_password(): void
    {
        $this->getPassword()->shouldReturn('password');
    }
}
