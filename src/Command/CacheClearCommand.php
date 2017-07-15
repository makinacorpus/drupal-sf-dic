<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Drupal flush all caches
 *
 * @codeCoverageIgnore
 */
class CacheClearCommand extends DrupalCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('drupal:cache')
            ->setAliases(['cc'])
            ->setDescription('Clears both the Drupal and Symfony caches')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        drupal_flush_all_caches();

        $io->success('All cache were cleared.');
    }
}
