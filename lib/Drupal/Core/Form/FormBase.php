<?php

namespace Drupal\Core\Form;

use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for forms.
 */
abstract class FormBase implements FormInterface
{
    use StringTranslationTrait;

    /**
     * @var \Drupal\Core\Logger\LoggerChannelFactory
     */
    private $loggerFactory;

    /**
     * {@inheritdoc}
     */
    static public function create(ContainerInterface $container)
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $formState)
    {
        // You probably should implement submit, but you also may set callables
        // on specific buttons #submit property, so this is optional too.
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        // Validation is optional.
    }

    /**
     * Gets the current user
     *
     * @return AccountProxy
     */
    protected function currentUser()
    {
        return \Drupal::currentUser();
    }

    /**
     * Gets the logger for a specific channel.
     *
     * @param string $channel
     *   The name of the channel.
     *
     * @return \Psr\Log\LoggerInterface
     *   The logger for this channel
     */
    protected function logger($channel)
    {
        if (!$this->loggerFactory) {
            $this->loggerFactory = \Drupal::service('logger.factory');
        }

        return $this->loggerFactory->get($channel);
    }
}
