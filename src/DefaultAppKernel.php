<?php

namespace MakinaCorpus\Drupal\Sf;

use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Default kernel, you might copy/paste it in your app/ folder and change it.
 */
class DefaultAppKernel extends Kernel
{
    public function registerBundles()
    {
        $ret = [];

        // Registering TwigBundle will provide a full Twig environement
        // for our Drupal site but won't have any major impact on the rest
        // so we can safely assume that our users will always want it
        if (class_exists('\Symfony\Bundle\TwigBundle\TwigBundle')) {
            $ret[] = new \Symfony\Bundle\TwigBundle\TwigBundle();
        }

        // But, for the next three, it sounds more complicated, this will
        // bring a lot of things in there they probably won't want, let's
        // just give them a choice to disable it
        if ($this->isFullStack) {
            if (class_exists('\Symfony\Bundle\FrameworkBundle\FrameworkBundle')) {
                $ret[] = new \Symfony\Bundle\FrameworkBundle\FrameworkBundle();
                $this->isFullStack = true;
            }
            if (class_exists('\Symfony\Bundle\MonologBundle\MonologBundle')) {
                $ret[] = new \Symfony\Bundle\MonologBundle\MonologBundle();
            }
            if (class_exists('\Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle')) {
                $ret[] = new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle();
            }
             if (class_exists('\Doctrine\Bundle\DoctrineBundle\DoctrineBundle')) {
                 $this->extraBundles[] = new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle();
             }
        }

        return $ret;
    }

    /**
     * {inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ($this->isFullStack) {

            // Reproduce the config_ENV.yml file from Symfony, but keep it
            // optional instead of forcing its usage
            $customConfigFile = $this->rootDir . '/config/config_' . $this->getEnvironment() . '.yml';
            if (!file_exists($customConfigFile)) {
                // Else attempt with a default one
                $customConfigFile = $this->rootDir . '/config/config.yml';
            }
            if (!file_exists($customConfigFile)) {
                // If no file is provided by the user, just use the default one
                // that provide sensible defaults for everything to work fine
                $customConfigFile = __DIR__ . '/../Resources/config/config.yml';
            }

            $loader->load($customConfigFile);
        }
    }
}
