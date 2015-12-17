<?php

namespace MakinaCorpus\Drupal\Sf\Container;

use Symfony\Component\DependencyInjection\Container as SymfonyContainer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Container extends SymfonyContainer
{
    /**
     * @inheritdoc
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        $this->services['service_container'] = $this;
    }
}
