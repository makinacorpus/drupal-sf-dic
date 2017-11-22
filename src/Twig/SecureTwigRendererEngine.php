<?php


namespace MakinaCorpus\Drupal\Sf\Twig;


use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormView;
use Twig\Environment;

/**
 * Class SecureTwigRendererEngine
 *
 * This "secure" engine just exists for the sake of being sure the symfony form template rendering actually gets escape
 * in a drupal twig environment, here goes:
 *
 * 1. This service gets called when a symfony form is rendered (top-btoom while compiling twig AST)
 * 2. The compiler pass automatically call setSecureEnvironment() with the twig engine, we take advantage of this to
 *    set the defualt escaping strategy to 'html'
 * 3. When the templates are actually rendered (but they are now compiled with escape) we put back the original strategy
 */
class SecureTwigRendererEngine extends TwigRendererEngine
{
    /**
     * Original escape strategy
     * @var string|bool
     */
    private $originalStrategy = false;

    /**
     * @var Environment
     */
    private $secureEnvironment = null;

    function setSecureEnvironment(Environment $environment)
    {
        $this->secureEnvironment = $environment;
        /** @var \Twig_Extension_Escaper $escaper */
        $escaper = $this->secureEnvironment->getExtension('Twig_Extension_Escaper');
        $this->originalStrategy = $escaper->getDefaultStrategy('html');
        $escaper->setDefaultStrategy('html');
    }

    public function renderBlock(FormView $view, $resource, $blockName, array $variables = [])
    {
        // Reset to previous strategy
        $this->secureEnvironment->getExtension('Twig_Extension_Escaper')->setDefaultStrategy($this->originalStrategy);

        return parent::renderBlock($view, $resource, $blockName, $variables);
    }
}
