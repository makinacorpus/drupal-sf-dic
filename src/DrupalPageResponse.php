<?php

namespace MakinaCorpus\Drupal\Sf;

use Symfony\Component\HttpFoundation\Response;

/**
 * Drupal response that builds the complete page arround.
 */
class DrupalPageResponse extends Response
{
    /**
     * Get Drupal default headers
     *
     * @see drupal_page_header()
     *
     * @param bool $isPageCacheable
     *
     * @return string[]
     */
    static final public function getDrupalDefaultHeaders($isPageCacheable = false)
    {
        $ret = [];
        $ret['Cache-Control'] = 'no-cache, must-revalidate';

        if (variable_get('omit_vary_cookie') && variable_get('kernel.set_vary_cookie', true)) {
            $ret['Vary'] = 'Cookie';
        }

        if (!$isPageCacheable) {
            // If set for a cacheable response, it will be used as the expiry
            // timestamp for the cache entry and will cause the cache item to
            // be dropped instantly in some cache backends, such as Redis.
            $ret['Expires'] = 'Sun, 19 Nov 1978 05:00:00 GMT';
        }

        if (!module_exists('seckit')) {
            // Seckit module already provide this option
            $ret['X-Content-Type-Options'] = 'nosniff';
        }

        return $ret;
    }

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
     * Is page cacheable
     *
     * @return bool
     */
    final private function isPageCacheable()
    {
        return variable_get('cache', 0) && drupal_page_is_cacheable();
    }

    /**
     * Handle cache manually
     */
    final private function cacheResponse()
    {
        global $base_root;

        $cache = (object)[
            'cid' => $base_root . request_uri(),
            'data' => [
                'path' => $_GET['q'],
                'body' => $this->getContent(),
                'title' => drupal_get_title(),
                'headers' => [],
                'page_compressed' => false,
            ],
            'expire' => CACHE_TEMPORARY,
            'created' => REQUEST_TIME,
        ];

        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $values = (array)$values;
            $cache->data['headers'][$name] = implode(', ', $values);
            if (strtolower($name) == 'expires') {
                $cache->expire = strtotime(reset($values));
            }
        }

        // When caching the response, we do need to fetch all headers from
        // current Drupal state. For example, modules such as SecKit do set
        // their headers during hook_init() - which means that if we don't
        // duplicate them from here, they will be lost in cached responses.
        $headerNames = _drupal_set_preferred_header_name();
        foreach (drupal_get_http_header() as $name => $value) {
            if (!$this->headers->has($name)) {
                $uniqueName = strtolower($name);
                $name = isset($headerNames[$uniqueName]) ? $headerNames[$uniqueName] : $name;
                $cache->data['headers'][$name] = $value;
            }
        }

        cache_set($cache->cid, $cache->data, 'cache_page', $cache->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        $isPageCacheable = $this->isPageCacheable();

        // We prevented Drupal from sending its own default headers, which is
        // good, but we need to restore them if Symfony's own headers do not
        // override them.
        foreach (self::getDrupalDefaultHeaders($isPageCacheable) as $name => $value) {
            if ('Vary' === $name) {
                // Merge vary with existing one
                if ($this->headers->has('Vary')) {
                    $currentVaryValue = (array)$this->headers->get('Vary');
                    $this->headers->set('Vary', implode(', ', array_merge([$value], $currentVaryValue)), true);
                } else {
                    $this->headers->set('Vary', $value);
                }
            } else if ('Cache-Control' === $name && !$this->headers->has($name)) {
                // I am really, really sorry, but if the response does not carry the
                // 'Cache-Control' header, it does set it onto itself in __construct
                // which means that the has() function will always return true.
                // Leave the Drupal default cache control no matter what.
                $this->headers->set($name, $value);
            }
        }

        // Do not let Drupal handle the cache itself, at this point, page has
        // already been renderered by Drupal, so we can safely rely on the
        // drupal_page_is_cacheable() function
        if ($isPageCacheable) {
            $this->cacheResponse();
        }

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
