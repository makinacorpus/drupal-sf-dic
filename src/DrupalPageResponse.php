<?php

namespace MakinaCorpus\Drupal\Sf;

use Symfony\Component\HttpFoundation\Response;

/**
 * Drupal response that builds the complete page arround.
 */
class DrupalPageResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    final public function setContent($content)
    {
        // Render directly the page since we are already at the end of the request.
        if (is_array($content) || !strripos($content, '</html>', 100)) {
            $content = drupal_render_page($content);
        }

        return parent::setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        parent::send();

        drupal_page_footer();

        return $this;
    }
}
