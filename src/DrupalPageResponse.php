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
        if (is_array($content) ||
            (($buffer = substr($content, -100)) && false === stripos($buffer, '</html>'))
        ) {
            $content = drupal_render_page($content);
        }

        return parent::setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        // We cannot directly call the parent implementation, because if Drupal
        // caches the result and sends it with drupal_serve_page_from_cache()
        // it will send headers in between.
        $this->sendHeaders();
        $this->sendContent();

        // We actually need to rewrite the drupal_page_footer() function
        // manually because we can't let it call the flush() method, everything
        // has already been flushed and it will trigger PHP notices.
        module_invoke_all('exit');
        drupal_session_commit();
        if (variable_get('cache', 0) && ($cache = drupal_page_set_cache())) {
          drupal_serve_page_from_cache($cache);
        }

        // And here it is for the output buffer flush, this means that
        // everything that happens during hook_exit() invokation and session
        // commit will happen before we loose the thread, sad Drupal is sad
        // but we have no other choice.
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }

        _registry_check_code(REGISTRY_WRITE_LOOKUP_CACHE);
        drupal_cache_system_paths();
        module_implements_write_cache();
        system_run_automated_cron();

        return $this;
    }
}
