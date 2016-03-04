<?php

namespace MakinaCorpus\Drupal\Sf\Twig\TFD;

class Environment extends \Twig_Environment
{
    protected $autoRender = false;

    public function __construct(\Twig_LoaderInterface $loader = null, $options = [])
    {
        if (!array_key_exists('autorender', $options)) {
            $options['autorender'] = true;
        }

        $this->autoRender = (bool)$options['autorender'];
        if ($this->autoRender) {
            $options['autoescape'] = false;
        }

        parent::__construct($loader, $options);
    }

    public function isAutoRender()
    {
        return $this->autoRender;
    }
}
