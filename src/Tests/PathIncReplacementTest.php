<?php

namespace MakinaCorpus\Drupal\Sf\Tests;

class PathIncReplacementTest extends AbstractDrupalTest
{
    protected function setUp()
    {
        parent::setUp();

        // Enfore the settings load in order to overrides the path.inc file
        drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);

        $GLOBALS['conf']['path_inc'] = __DIR__ . '/../sf_dic.path.inc';
    }

    public function testItBootstraps()
    {
        // This enforces a full bootstrap
        $this->getDrupalContainer();
    }

    // @todo test basic functions
}
