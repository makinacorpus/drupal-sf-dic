<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestStackTest extends AbstractDrupalTest
{
    public function testRequestStack()
    {
        $container = $this->getDrupalContainer();

        $this->assertTrue($container->has('request_stack'));
        $this->assertTrue(\Drupal::hasService('request_stack'));

        /* @var $requestFromThis RequestStack */
        $requestFromThis = $container->get('request_stack');
        $requestFromThat = \Drupal::service('request_stack');
        $requestFromWhat = \Drupal::getContainer()->get('request_stack');

        $this->assertSame($requestFromThat, $requestFromThis);
        $this->assertSame($requestFromThat, $requestFromWhat);

        $current = $requestFromThis->getCurrentRequest();
        $master  = $requestFromThis->getMasterRequest();
        // We are NOT in a subrequest
        $this->assertSame($current, $master);
    }
}
