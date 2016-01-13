<?php

namespace Drupal\Core\Form;

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
    public static function create(ContainerInterface $container)
    {
        return new static();
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
     * @return stdClass
     *   User account
     */
    protected function currentUser()
    {
        return user_load($GLOBALS['user']->uid);
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
            \Drupal::service('logger.factory');
        }

        return $this->loggerFactory->get($channel);
    }
}
