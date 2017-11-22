<?php

namespace MakinaCorpus\Drupal\Sf\Twig;

class Environment extends \Twig_Environment
{
    protected $autoRender = false;

    /**
     * Environment constructor.
     *
     * @see \MakinaCorpus\Drupal\Sf\Twig\SecureTwigRendererEngine
     */
    public function __construct(\Twig_LoaderInterface $loader = null, $options = [])
    {
        if (!array_key_exists('autorender', $options)) {
            $options['autorender'] = true;
        }

        $this->autoRender = (bool)$options['autorender'];
        if ($this->autoRender) {
            // Sad but true story, the TFD7 implementation does filter calls
            // everywhere, while I would prefer to keep the autoescape for
            // security, I can't because all templates generated using the TFD
            // extension enabled will be *fully* escape (WTF, seriously)...
            $options['autoescape'] = false;
        }

        parent::__construct($loader, $options);
    }

    public function isAutoRender()
    {
        return $this->autoRender;
    }
}
