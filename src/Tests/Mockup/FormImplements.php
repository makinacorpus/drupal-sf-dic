<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FormImplements implements FormInterface
{
    static public function create(ContainerInterface $container)
    {
        return new self();
    }

    public function getFormId()
    {
        return 'i_am_a_form_that_implements_form_interface';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        return [
            'actions' => [
                '#type' => 'actions',
                'submit_normal' => [
                    '#type' => 'submit',
                    '#value' => 'Submit normal',
                ],
                'submit_other' => [
                    '#type' => 'submit',
                    '#value' => 'Submit other',
                    '#submit' => [
                        '::submitFormOther',
                    ],
                ],
            ],
        ];
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message("I have been validated");
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message("Normal submit");
    }

    public function submitFormOther(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message("Other submit");
    }
}
