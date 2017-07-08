<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Bootstraps Drupal before execution
 */
abstract class DrupalCommand extends ContainerAwareCommand
{
    public function run(InputInterface $input, OutputInterface $output)
    {
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

        return parent::run($input, $output);
    }
}
