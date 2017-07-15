<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Unit\Controller;

use MakinaCorpus\Drupal\Sf\Controller\ArgumentResolver\RequestQueryValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Test request GET/POST value resolver
 */
class ValueResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test everything at once
     */
    public function testQueryValueResolver()
    {
        $resolver = new RequestQueryValueResolver();
        $request = new Request(['string' => 'foo', 'int' => '4'], ['float' => '4.2', 'bool' => '1', 'no_type' => 'bar']);

        $arguments = [
            $boolArgument   = new ArgumentMetadata('bool', 'bool', false, false, null),
            $intArgument    = new ArgumentMetadata('int', 'int', false, false, null),
            $floatArgument  = new ArgumentMetadata('float', 'float', false, false, null),
            $stringArgument = new ArgumentMetadata('string', 'string', false, false, null),
            $noTypeArgument = new ArgumentMetadata('no_type', null, false, false, null),
        ];

        $unsupportedArguments = [
            new ArgumentMetadata('nope_bool', 'bool', false, false, null),
            new ArgumentMetadata('nope_int', 'int', false, false, null),
            new ArgumentMetadata('nope_float', 'float', false, false, null),
            new ArgumentMetadata('nope_string', 'string', false, false, null),
            new ArgumentMetadata('nope_stupid', 'stupid', false, false, null),
        ];

        foreach ($arguments as $argument) {
            $this->assertTrue($resolver->supports($request, $argument));
        }
        foreach ($unsupportedArguments as $argument) {
            $this->assertFalse($resolver->supports($request, $argument));
        }

        $this->assertSame([true], iterator_to_array($resolver->resolve($request, $boolArgument)));
        $this->assertSame([4], iterator_to_array($resolver->resolve($request, $intArgument)));
        $this->assertSame([4.2], iterator_to_array($resolver->resolve($request, $floatArgument)));
        $this->assertSame(['foo'], iterator_to_array($resolver->resolve($request, $stringArgument)));
        $this->assertSame(['bar'], iterator_to_array($resolver->resolve($request, $noTypeArgument)));
    }
}
