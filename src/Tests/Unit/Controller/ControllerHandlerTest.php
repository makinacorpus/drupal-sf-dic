<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Unit\Controller;

use MakinaCorpus\Drupal\Sf\Controller\ControllerHandler;
use MakinaCorpus\Drupal\Sf\Http\NullControllerResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Tests symfony controllers integration in Drupal
 */
class ControllerHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $exitCalled = false;

    /**
     * @param ContainerInterface $container
     * @param bool $doHandleExceptions
     *
     * @return ControllerHandler
     */
    private function createInstance(ContainerInterface $container, $doHandleExceptions = false)
    {
        $dispatcher = new EventDispatcher();
        $resolver = new NullControllerResolver();

        return new ControllerHandler(new ArgumentResolver(), $container, $dispatcher, new HttpKernel($dispatcher, $resolver), [$this, 'exitCallback'], $doHandleExceptions);
    }

    /**
     * For testing the exit callback
     */
    public function exitCallback()
    {
        $this->exitCalled = true;
    }

    /**
     * Handle normal response
     */
    public function testHandleNormalResponse()
    {
        $container = new Container();
        $handler = $this->createInstance($container);

        $this->exitCalled = false;
        $response = new Response("this is a normal response");
        $this->assertSame("this is a normal response", $handler->prepareResponseForDrupal(new Request(), $response));
        $this->assertFalse($this->exitCalled);
    }

    /**
     * Handle normal response
     */
    public function testHandleNormalResponseWithHtml()
    {
        $container = new Container();
        $handler = $this->createInstance($container);

        ob_start();
        $this->exitCalled = false;
        $response = new Response("<html>this is a normal response</html>");
        $this->assertNull($handler->prepareResponseForDrupal(new Request(), $response));
        $this->assertSame("<html>this is a normal response</html>", ob_get_clean());
        $this->assertTrue($this->exitCalled);
    }

    /**
     * Deprecated int response (Drupal 7 menu router return constants)
     */
    public function testHandleIntResponse()
    {
        $container = new Container();
        $handler = $this->createInstance($container);

        $this->exitCalled = false;
        $this->assertSame(1, $handler->prepareResponseForDrupal(new Request(), 1));
        $this->assertSame(2, $handler->prepareResponseForDrupal(new Request(), 2));
        $this->assertFalse($this->exitCalled);
    }

    /**
     * Deprecated render array as a response (Drupal 7 compatibility)
     */
    public function testHandleRenderArrayResponse()
    {
        $container = new Container();
        $handler = $this->createInstance($container);

        $this->exitCalled = false;
        $this->assertSame(['#markup' => "foo"], $handler->prepareResponseForDrupal(new Request(), ['#markup' => "foo"]));
        $this->assertFalse($this->exitCalled);
    }

    /**
     * For every implementation specific response, it should not attempt to
     * wrap it up into a Drupal page: hence the next series of tests
     */
    public function testHandleSpecificResponse()
    {
        $container = new Container();
        $handler = $this->createInstance($container);

        ob_start();
        $this->exitCalled = false;
        $response = new JsonResponse(['foo' => 'bar']);
        $this->assertNull($handler->prepareResponseForDrupal(new Request(), $response));
        $this->assertSame('{"foo":"bar"}', ob_get_clean());
        $this->assertTrue($this->exitCalled);

        ob_start();
        $this->exitCalled = false;
        $response = new BinaryFileResponse(__FILE__);
        $this->assertNull($handler->prepareResponseForDrupal(new Request(), $response));
        ob_get_clean();
        $this->assertTrue($this->exitCalled);

        ob_start();
        $this->exitCalled = false;
        $response = new StreamedResponse('mt_rand');
        $this->assertNull($handler->prepareResponseForDrupal(new Request(), $response));
        ob_get_clean();
        $this->assertTrue($this->exitCalled);

        ob_start();
        $this->exitCalled = false;
        $response = new Response('{"foo":"bar"}');
        $response->headers->set('Content-Type', 'application/json');
        $this->assertNull($handler->prepareResponseForDrupal(new Request(), $response));
        $this->assertSame('{"foo":"bar"}', ob_get_clean());
        $this->assertTrue($this->exitCalled);

        ob_start();
        $this->exitCalled = false;
        $response = new Response('<pouet></pouet>');
        $response->headers->set('Content-Type', 'application/xml');
        $this->assertNull($handler->prepareResponseForDrupal(new Request(), $response));
        $this->assertSame('<pouet></pouet>', ob_get_clean());
        $this->assertTrue($this->exitCalled);
    }

    public function testPrepareResponseForDrupalWithError()
    {
        $this->markTestIncomplete();
    }

    public function testHandle()
    {
        $this->markTestIncomplete();
    }

    public function testExecuteWithCallback()
    {
        $this->markTestIncomplete();
    }

    public function testExecuteWithServiceId()
    {
        $this->markTestIncomplete();
    }

    /**
     * Using default controller method
     */
    public function testExecuteWithDefaultMethod()
    {
        $container = new Container();
        $request = new Request();
        $handler = $this->createInstance($container);

        $response = $handler->execute(MethodController::class, $request, [], 'myDefaultMethod');
        $this->assertSame("default method ok", $response->getContent());
    }

    /**
     * Exceute a method that has the suffix in its name, but called without
     */
    public function testExecuteWithSuffix()
    {
        $container = new Container();
        $request = new Request();
        $handler = $this->createInstance($container);

        $response = $handler->execute(MethodController::class.'::methodWith', $request, [], null, 'Suffix');
        $this->assertSame("with suffix ok", $response->getContent());
    }

    /**
     * Execute a method that has no suffix
     */
    public function testExecuteWithoutSuffix()
    {
        $container = new Container();
        $request = new Request();
        $handler = $this->createInstance($container);

        $response = $handler->execute(MethodController::class.'::methodWithoutSuffix', $request);
        $this->assertSame("without suffix ok", $response->getContent());
    }

    /**
     * Argument resolver test using Request attributes as controller parameters
     */
    public function testArgumentResolver()
    {
        $container = new Container();
        $request = new Request([], [], ['foo' => "Foo", 'bar' => 7]);
        $handler = $this->createInstance($container);

        $response = $handler->execute(MethodController::class.'::methodUsingArgumentResolver', $request);
        $this->assertSame("Foo:7", $response->getContent());
    }

    /**
     * Tests various use case of the argument fallback
     */
    public function testArgumentResolverFallback()
    {
        $container = new Container();
        $request = new Request();
        $handler = $this->createInstance($container);

        // Normal use case, with a request
        $response = $handler->execute(MethodController::class.'::methodUsingArgumentFallback', $request, [3, 5]);
        $this->assertSame("8", $response->getContent());

        // Force container to have no arguments at all, should be a 404
        try {
            $response = $handler->execute(MethodController::class.'::methodUsingArgumentFallbackThatWillFail', $request);
            $this->fail();
        } catch (NotFoundHttpException $e) {
            $this->assertTrue(true);
        }

        // Force container to have missing arguments, a type error is expected
        // when using PHP7 for testing
        try {
            $response = $handler->execute(MethodController::class.'::methodUsingArgumentFallback', $request, [3]);
            $this->fail();
        } catch (\TypeError $e) {
            $this->assertTrue(true);
        }
    }
}
