<?php
/*
 * This is file is a raw copy of the one provided by the Symfony framework.
 * All credits to its authors.
 *
 * We have to change it, because the original file does not rely upon the
 * Symfony\Component\Config\FileLocatorInterface interface but directly
 * reference the Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator
 * class instead, making our own implementation crashing the system, since
 * it does not extends the framework's one.
 *
 * Small note: this will configured/overrided by the provided
 * MakinaCorpus\Drupal\Sf\Container\DependencyInjection\Compiler\TwigCompilerPass
 * compiler pass, because we cannot hardcoded it, it cannot be used without
 * the framework bundle enabled.
 *
 * Original credits/licensing lives below.
 * --
 *
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MakinaCorpus\Drupal\Sf\CacheWarmer;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

/**
 * Computes the association between template names and their paths on the disk.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplatePathsCacheWarmer extends CacheWarmer
{
    protected $finder;
    protected $locator;

    /**
     * Constructor.
     *
     * @param TemplateFinderInterface $finder
     *   The template finder
     * @param FileLocatorInterface $locator
     *   The template locator
     */
    public function __construct(TemplateFinderInterface $finder, FileLocatorInterface $locator)
    {
        $this->finder = $finder;
        $this->locator = $locator;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $templates = array();

        /** @var \Symfony\Component\Templating\TemplateReferenceInterface $template */
        foreach ($this->finder->findAllTemplates() as $template) {
            // This will actually crash if the locator is not a template
            // locator. This is somehow stupid that the template locator breaks
            // its interface signature, but we have no choice than deal with it
            // at ths point.
            $templates[$template->getLogicalName()] = $this->locator->locate($template);
        }

        $this->writeCacheFile($cacheDir.'/templates.php', sprintf('<?php return %s;', var_export($templates, true)));
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
