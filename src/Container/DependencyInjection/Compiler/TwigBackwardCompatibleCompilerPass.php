<?php

namespace MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler;

use MakinaCorpus\Drupal\Sf\Templating\Loader\TemplateLocator as FallbackTemplateLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Fixes Twig configuration to be more resilient and more friendly with the
 * original Symfony framework.
 */
class TwigBackwardCompatibleCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // If Symfony is present, do not override Symfony's services with our
        // and use the original ones instead, thus leaving untouched their
        // original class in definition, and allow side components, such as
        // Symfony's cache warmers to have the input they are waiting for.
        if (class_exists('Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator') && $container->hasDefinition('templating.locator')) {
            $locatorDefinition = $container->getDefinition('templating.locator');
            $locatorClass = $container->getParameterBag()->resolveValue($locatorDefinition->getClass());
            $locatorClass = ltrim($locatorClass, '\\');
            // Do not override if the class is not our own, this ould mean
            // someone else had overriden it instead of us, and I don't want
            // to make users unhappy.
            if ($locatorClass === FallbackTemplateLocator::class) {
                $locatorDefinition->setClass('Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator');
            }
        }
    }

    /**
     * Register Drupal namespaces in Twig
     */
    private function registerDrupalNamespaces(ContainerBuilder $container)
    {
        $id = 'twig.loader.filesystem';
        if ($container->hasAlias($id)) {
            $id = (string)$container->getAlias($id);
        }
        if (!$container->hasDefinition($id)) {
            return;
        }
        $definition = $container->getDefinition($id);

        $result = \Database::getConnection()->query("SELECT name, filename FROM {system} WHERE status = 1 AND (filename LIKE 'sites/%' OR filename LIKE 'profiles/%')");
        foreach ($result as $row) {
            $definition->addMethodCall('addPath', [DRUPAL_ROOT.'/'.dirname($row->filename), $row->name]);
        }
    }
}
