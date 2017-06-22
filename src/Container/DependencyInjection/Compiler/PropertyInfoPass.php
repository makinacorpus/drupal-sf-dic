<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds extractors to the property_info service.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 *
 * --
 * This is a baremetal copy of the original Symfony 3.2 code.
 */
class PropertyInfoPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('property_info')) {
            return;
        }

        $definition = $container->getDefinition('property_info');

        $listExtractors = $this->findAndSortTaggedServices('property_info.list_extractor', $container);
        $definition->replaceArgument(0, $listExtractors);

        $typeExtractors = $this->findAndSortTaggedServices('property_info.type_extractor', $container);
        $definition->replaceArgument(1, $typeExtractors);

        $descriptionExtractors = $this->findAndSortTaggedServices('property_info.description_extractor', $container);
        $definition->replaceArgument(2, $descriptionExtractors);

        $accessExtractors = $this->findAndSortTaggedServices('property_info.access_extractor', $container);
        $definition->replaceArgument(3, $accessExtractors);
    }
}
