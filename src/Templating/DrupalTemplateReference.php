<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MakinaCorpus\Drupal\Sf\Templating;

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

class DrupalTemplateReference extends BaseTemplateReference
{
    public function __construct($type, $name, $path, $format, $engine)
    {
        $this->parameters = array(
            'type'    => $type,
            'name'    => $name,
            'path'    => $path,
            'format'  => $format,
            'engine'  => $engine,
        );
    }

    public function getPath()
    {
        return
            DRUPAL_ROOT
                . '/' . drupal_get_path($this->parameters['type'], $this->parameters['name'])
                . '/' . $this->parameters['path']
                . '.' . $this->parameters['format']
                . '.' . $this->parameters['engine']
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf('%s:%s:%s.%s.%s', $this->parameters['type'], $this->parameters['name'], $this->parameters['path'], $this->parameters['format'], $this->parameters['engine']);
    }
}
