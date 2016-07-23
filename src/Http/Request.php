<?php

namespace MakinaCorpus\Drupal\Sf\Http;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Aliases won't work when we are dealing directly with kernel, because
 * router will attempt to match alias a if it was a valid URL, we need to
 * replace it first.
 */
class Request extends BaseRequest
{
    private $isSecure;

    /**
     * {@inheritDoc}
     */
    protected function prepareRequestUri()
    {
        if (isset($_GET['q'])) {

            // Allow the router to correctly match paths
            $requestUri = '/' . $_GET['q'];
            $this->server->set('REQUEST_URI', $requestUri);

            // Query string is used by some request listeners for the
            // framework process process, with q= they will fail
            $queryString = $this->server->get('QUERY_STRING');
            $queryString = preg_replace('/q=[^&]*/', '', $queryString);
            if ($queryString && '&' === $queryString[0]) {
                if ('&' === $queryString) {
                    $queryString = '';
                } else {
                    $queryString = substr($queryString, 1);
                }
            }
            $this->server->set('QUERY_STRING', $queryString);

        } else {
            $requestUri = parent::prepareRequestUri();
        }

        return $requestUri;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryString()
    {
        // Force prepareRequestUri() to be called before query string is fetched
        $this->getRequestUri();

        return parent::getQueryString();
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure()
    {
        if (null !== $this->isSecure) {
            return $this->isSecure;
        }
        if (parent::isSecure()) {
            return $this->isSecure = true;
        }

        // It may happen that some misconfigured environments won't set
        // the $_SERVER['HTTPS'] variable, we need another way to detect
        // this, either using Drupal, or port in use.
        if (443 === (int)$this->server->get('SERVER_PORT', 80)) {
            return $this->isSecure = true;
        }

        // Worst than misconfigured, we actually have no idea about what's
        // what because FPM or any other CGI gives us nothing goog to read,
        // let's just trust Drupal and pray.
        if ($GLOBALS['is_https']) {
            return $this->isSecure = true;
        }

        // The Drupal base URL might have been hardcoded in settings.php so
        // let's use that as last resort.
        if ('https://' === substr($GLOBALS['base_url'], 0, 8)) {
            return $this->isSecure = true;
        }

        return $this->isSecure = false;
    }
}
