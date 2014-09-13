<?php

namespace spec\Neoxygen\NeoClient\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Neoxygen\NeoClient\HttpClient\GuzzleHttpClient;

class SimpleCommandSpec extends ObjectBehavior
{
    function let(GuzzleHttpClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\NeoClient\Command\SimpleCommand');
    }
}
