<?php

namespace MakinaCorpus\Drupal\Sf\Tests\Mockup;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FormExtends extends FormBase
{
    public function getFormId()
    {
        return 'i_am_a_form_that_extends_form_base';
    }

    public function buildForm(array $form, FormStateInterface $form_state, $someParam = null)
    {
        return [
            'some_param' => [
                '#type' => 'textfield',
                '#default_value' => $someParam,
            ],
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

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message("Normal submit, some param is " . $form_state->getValue('some_param'));
    }

    public function submitFormOther(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message("Other submit");
    }
}
