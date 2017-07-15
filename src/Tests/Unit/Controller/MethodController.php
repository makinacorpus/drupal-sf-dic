<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Unit\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MethodController
{
    use ContainerAwareTrait;

    public function hasContaier()
    {
        return !!!$this->container;
    }

    /**
     * Using default method name
     */
    public function myDefaultMethod()
    {
        return new Response("default method ok");
    }

    /**
     * Not found
     */
    public function notFoundAction()
    {
        throw new NotFoundHttpException();
    }

    /**
     * Access denied
     */
    public function deniedAction()
    {
        throw new AccessDeniedHttpException();
    }

    /**
     * Using a method wihtout the "Action" suffix
     */
    public function methodWithoutSuffix()
    {
        return new Response("without suffix ok");
    }

    /**
     * Using a method wihtout the "Action" suffix
     */
    public function methodWithSuffix()
    {
        return new Response("with suffix ok");
    }

    /**
     * Using the argument resolver, setting Request attributes that are named
     * using the method arguments should run OK using the argument resolver.
     */
    public function methodUsingArgumentResolver(string $foo, Request $request, int $bar)
    {
        return new Response($foo.':'.$bar);
    }

    /**
     * Nothing in this will be resolved by the argument resolver, but by the
     * legacy Drupal menu item fallback instead.
     */
    public function methodUsingArgumentFallback(int $a, Request $request, int $b)
    {
        return new Response($a + $b);
    }

    /**
     * We do not set any Request parameter here, we want it to fail with a 404
     * error, and when there is at least one Request argument it will fail with
     * either a PHP notice (PHP<7) or a TypeError (PHP>=7).
     */
    public function methodUsingArgumentFallbackThatWillFail(int $a, int $b)
    {
        throw new \LogicException("I should not be called.");
    }

    /**
     * When only the Request is necessary, it will work in all cases, the
     * argument resolver will take over, but it will also work using the
     * fallback if no resolver exist.
     */
    public function anActionWithRequest(Request $request)
    {
        return new Response("It works");
    }
}
