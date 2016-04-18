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
            $requestUri = '/' . $_GET['q'];
            $this->server->set('REQUEST_URI', $requestUri);
        } else {
            $requestUri = parent::prepareRequestUri();
        }

        return $requestUri;
    }
}
