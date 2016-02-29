<?php

/**
 * @file
 * Contains \Drupal\Core\Form\FormBuilder.
 */

namespace Drupal\Core\Form;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
final class FormBuilder implements FormBuilderInterface
{
    /**
     * @var FormInterface[]
     */
    private $formMap = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Default constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Get form instance
     *
     * This is NOT part of Drupal 8 API but serves for bridging with Drupal 7 API.
     *
     * @param string $formId
     *
     * @return mixed[]
     *   A tuple where first value is a FormInterface instance and second value
     *   is the associated FormStateInterface instance, of course it'll return
     *   null if nothing is set at this key
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
    public function getForm($formClass)
    {
        $args = func_get_args();
        array_shift($args);

        if (!class_exists($formClass)) {
            $this->logger->log(LogLevel::CRITICAL, "Form class '@class' does not exists", ['@class' => $formClass]);
            return [];
        }
        if (!method_exists($formClass, 'create')) {
            $this->logger->log(LogLevel::CRITICAL, "Form class '@class' does not implements ::create()", ['@class' => $formClass]);
            return [];
        }

        // God, I do hate Drupal 8...
        $form = call_user_func([$formClass, 'create'], $this->container);

        if (!$form instanceof FormInterface) {
            $this->logger->log(LogLevel::CRITICAL, "Form class '@class' does not implement \Drupal\Core\Form\FormInterface", ['@class' => $formClass]);
            return [];
        }

        $formId = $form->getFormId();

        $data = [];
        $data['build_info']['args'] = $args;

        $formState = new FormState($data);
        $formState->setFormObject($form);

        $this->formMap[$formId] = [$form, $formState];

        return drupal_build_form($formId, $data);
    }
}
