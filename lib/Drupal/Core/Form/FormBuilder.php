<?php

/**
 * @file
 * Contains \Drupal\Core\Form\FormBuilder.
 */

namespace Drupal\Core\Form;

use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Psr\Log\LogLevel;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
final class FormBuilder implements FormBuilderInterface
{
    /**
     * @var \FormInterface[]
     */
    private $formMap = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Default constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get form instance
     *
     * This is NOT part of Drupal 8 API but serves for bridging with Drupal 7 API.
     *
     * @param string $formId
     *
     * @return FormInterface
     *   Or null if not found
     */
    public function getFormInstance($formId)
    {
        if (isset($this->formMap[$formId])) {
            return $this->formMap[$formId];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getForm($formClass, ...$args)
    {
        if (!class_exists($formClass)) {
            $this->logger->log(LogLevel::CRITICAL, "Form class '@class' does not exists", ['@class' => $formClass]);
            return [];
        }

        $form = new $formClass();
        if (!$form instanceof FormInterface) {
            $this->logger->log(LogLevel::CRITICAL, "Form class '@class' does not implement \Drupal\Core\Form\FormInterface", ['@class' => $formClass]);
            return [];
        }

        $formId = $form->getFormId();
        $this->formMap[$formId] = $form;
        array_unshift($args, $formId);

        return call_user_func_array('drupal_get_form', $args);
    }
}
