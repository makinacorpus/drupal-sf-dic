<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Drop the current kernel
 *
 * @codeCoverageIgnore
 */
class DropKernelCacheCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cache:kernel')
            ->setDescription('Clears the Symfony kernel cache (current one being in use)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $kernel = $this->getApplication()->getKernel();

        $cacheDir = $kernel->getContainer()->getParameter('kernel.cache_dir');
        $filesystem = $kernel->getContainer()->get('filesystem');

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory', $cacheDir));
        }

        if ($filesystem->exists($cacheDir)) {
            $filesystem->remove($cacheDir);
        }

        $io->success(sprintf('Cache for the "%s" environment (debug=%s) was successfully cleared.', $kernel->getEnvironment(), var_export($kernel->isDebug(), true)));
    }
}
