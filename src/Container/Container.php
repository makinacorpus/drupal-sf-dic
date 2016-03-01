<?php

namespace MakinaCorpus\Drupal\Sf\Container;

use Symfony\Component\DependencyInjection\Container as SymfonyContainer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use MakinaCorpus\Drupal\Sf\Container\DependencyInjection\ParameterBag\DrupalFrozenParameterBag;

class Container extends SymfonyContainer
{
    /**
     * @inheritdoc
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        // This is not elegant, but it works.
        parent::__construct(
            // This allows to resolve Drupal variables as parameters
            new DrupalFrozenParameterBag(
                $parameterBag->all()
            )
        );

        $this->services['service_container'] = $this;
    }
}
