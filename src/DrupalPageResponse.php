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

        // We actually need to rewrite the drupal_page_footer() function
        // manually because we can't let it call the flush() method, everything
        // has already been flushed and it will trigger PHP notices.
        module_invoke_all('exit');
        drupal_session_commit();
        if (variable_get('cache', 0) && ($cache = drupal_page_set_cache())) {
          drupal_serve_page_from_cache($cache);
        }
        _registry_check_code(REGISTRY_WRITE_LOOKUP_CACHE);
        drupal_cache_system_paths();
        module_implements_write_cache();
        system_run_automated_cron();

        return $this;
    }
}
