<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\EventDispatcher\Event;

final class FormEvent extends Event
{
    const EVENT_ALTER = 'form:alter';
    const EVENT_ALTER_PREFIX = 'form:alter:';

    private $form;
    private $formState;

    /**
     * Constructor
     *
     * @param FormInterface $form
     * @param FormStateInterface $formState
     */
    public function __construct(FormInterface $form, FormStateInterface $formState)
    {
        $this->form = $form;
        $this->formState = $formState;
    }

    /**
     * Get form
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get form state
     *
     * @return FormStateInterface
     */
    public function getFormState()
    {
        return $this->formState;
    }

    /**
     * Get form identifier
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->form->getFormId();
    }
}
