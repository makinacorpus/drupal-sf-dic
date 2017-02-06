<?php

namespace MakinaCorpus\Drupal\Sf\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extended because in Drupal context, we need to restore the output buffering
 * before sending the response: if we do not, various Drupal shutdown handlers
 * will cause infamous "output buffer already started" errors.
 */
class ExceptionController
{
    private $exceptionController;

    /**
     * Default constructor
     *
     * @param BaseExceptionController $exceptionController
     */
    public function __construct(BaseExceptionController $exceptionController)
    {
        $this->exceptionController = $exceptionController;
    }

    /**
     * Decorates BaseExceptionController::showAction()
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $response = $this->exceptionController->showAction($request, $exception, $logger);

        // Avoid Drupal errors.
        ob_start();

        return $response;
    }
}
