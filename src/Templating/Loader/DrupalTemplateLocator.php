<?php

namespace MakinaCorpus\Drupal\Sf\Templating\Loader;

use MakinaCorpus\Drupal\Sf\Templating\DrupalTemplateReference;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\Storage\FileStorage;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Locates overriden templates in the following places in order:
 *   - CURRENT_THEME_PATH/EXTENSION_NAME/REST_OF_NAME
 *   - APP_DIR/Resources/EXTENSION_NAME/REST_OF_NAME (not implemented yet)
 *   - EXTENSION_PATH/views/REST_OF_NAME
 *   - EXTENSION_PATH/REST_OF_NAME
 *
 * Where:
 *   - CURRENT_THEME_PATH is the path of the current theme, no matter its
 *     engine, it will be treated as a twig template in all cases;
 *   - EXTENSION_NAME is the name of the module or theme the original template
 *     is provided by;
 *   - EXTENSION_PATH is the full path of the module or theme the original
 *     template is provided by;
 *   - REST_OF_NAME is what follows the extension name, if you have more
 *     following ':' they will be converted as '/' in path lookup.
 *
 * @deprecated
 *   Will be removed next version.
 */
class DrupalTemplateLocator implements FileLocatorInterface
{
    /**
     * @var FileLocatorInterface
     */
    private $decorated;

    /**
     * @var string[]
     */
    private $cache = [];

    public function __construct(FileLocatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof DrupalTemplateReference) {
            if ($this->decorated) {
                return $this->decorated->locate($template, $currentPath, $first);
            }

            throw new \InvalidArgumentException('The template must be an instance of TemplateReferenceInterface.');
        }

        @trigger_error('You should not use path such as "[module|theme]:[NAME]:/[PATH].html.twig and use namespaces instead "@[NAME]/[PATH].html.twig where [PATH] is the module or theme name"', E_USER_DEPRECATED);

        $name = $template->getLogicalName();

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $type = $template->get('type');
        $extension = $template->get('name');
        $restOfPath = str_replace(':', '/', $template->getRestOfPath());

        // We don't need to attempt to find a theme overriding itself, it
        // would relatively stupid, just skip this if extension is the current
        // theme.
        if ($extension !== $GLOBALS['theme']) {
            $currentThemePath = drupal_get_path('theme', $GLOBALS['theme']) . '/' . $extension . '/' . $restOfPath;
            if (file_exists($currentThemePath)) {
                return new FileStorage($currentThemePath);
            }
        }

        $extensionPath = drupal_get_path($type, $extension);

        // The "views" sub-folder does not applies to theme, it's an helper
        // for organizing modules code only, let's skip this useless check.
        if ('theme' !== $type) {
            $inViewsPath = $extensionPath . '/views/' . $restOfPath;
            if (file_exists($inViewsPath)) {
                return new FileStorage($inViewsPath);
            }
        }

        $notInViewsPath = $extensionPath . '/' . $restOfPath;
        if (file_exists($notInViewsPath)) {
            return new FileStorage($notInViewsPath);
        }

        throw new \RuntimeException(
            sprintf(
                "Unable to find template '%s' in any of: '%s'",
                $template,
                implode(', ', [$currentThemePath, $inViewsPath, $notInViewsPath])
            )
        );
    }
}
