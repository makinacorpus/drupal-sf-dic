<?php

namespace MakinaCorpus\Drupal\Sf\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Fixes heavily broken Drupal sites
 *
 * @codeCoverageIgnore
 */
class PanicCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('drupal:panic')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, "Drupal hostname", '127.0.0.1')
            ->setAliases(['panic'])
            ->setDescription('Manually clear caches and rebuild Drupal essentials such as its class registry')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->note("Initializing degraded Drupal environment");

        // Set a few things for Drupal not to yell
        $_SERVER['HTTP_HOST'] = $input->getOption('host');

        // Important, Drupal will do stuff from this
        chdir(DRUPAL_ROOT);

        // Force minimal required includes, we are going to run very low
        // level Drupal caches rebuild without bootstrapping it, in order
        // to avoid crashes.
        require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
        require_once DRUPAL_ROOT . '/includes/database/database.inc';
        require_once DRUPAL_ROOT . '/includes/cache.inc';
        require_once DRUPAL_ROOT . '/includes/file.inc';
        require_once DRUPAL_ROOT . '/includes/module.inc';
        require_once DRUPAL_ROOT . '/includes/registry.inc';

        $io->note(sprintf("working in %s", conf_path()));
        drupal_environment_initialize();
        drupal_settings_initialize();
        // restore_error_handler();
        // restore_exception_handler();
        _drupal_bootstrap_database();

        $io->note("Cache system enable attempt");
        if (!empty($GLOBALS['conf']['cache_backends'])) {
          foreach ($GLOBALS['conf']['cache_backends'] as $include) {
            $io->note(sprintf("Requiring %s", $include));
            if (include_once DRUPAL_ROOT . '/' . $include) {
              $io->error("Could not require %s", $include);
            }
          }
        }

        drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES);

        $io->note("system_rebuild_module_data()");
        require_once DRUPAL_ROOT . '/includes/common.inc';
        require_once DRUPAL_ROOT . '/modules/system/system.module';
        drupal_static_reset('system_rebuild_module_data');
        _system_rebuild_module_data();
        system_rebuild_module_data();

        $io->note("system_rebuild_theme_data()");
        require_once DRUPAL_ROOT . '/includes/theme.inc';
        system_rebuild_theme_data();

        $io->note("_registry_update()");
        _registry_update();

        $io->success('All done');
    }
}
