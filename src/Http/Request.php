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
            if ('&' === $queryString[0]) {
                $queryString = substr($queryString, 1);
            }
            $this->server->set('QUERY_STRING', $queryString);

        } else {
            $requestUri = parent::prepareRequestUri();
        }

        return $requestUri;
    }
}
