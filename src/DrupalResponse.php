<?php

namespace MakinaCorpus\Drupal\Sf;

use Symfony\Component\HttpFoundation\Response;

/**
 * Very specific response that will convert Drupal render arrays on send()
 */
class DrupalResponse extends Response
{
    protected $drupalContent;
    protected $renderedContent;
    private $headerMerged = false;

    /**
     * Constructor.
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
    }

    /**
     * This is basically a copy/paste of drupal_send_headers() that return the
     * headers instead of sending it
     *
     * @return string[]
     */
    private function getAndNormalizeDrupalHeaders()
    {
        $ret      = [];
        $headers  = drupal_get_http_header();
        $names    = _drupal_set_preferred_header_name();

        foreach ($headers as $loweredName => $value) {
            if (false !== $value) {
                $ret[$names[$loweredName]] = $value;
            }
        }

        return $ret;
    }

    /**
     * Merge headers from Drupal
     */
    private function mergeDrupalHeaders()
    {
        if (headers_sent() || $this->headerMerged) {
            return;
        }

        $this->headerMerged = true;

        // Drupal headers have already been sent.
        // $this->headers->add($this->getAndNormalizeDrupalHeaders());

        // Drupal default return Content-Type if none already set
        if (null !== $this->drupalContent) {
            if (!$this->headers->has('Content-Type')) {
                $this->headers->set('Content-Type', 'text/html; charset=utf-8');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function sendHeaders()
    {
        $this->mergeDrupalHeaders();

        return parent::sendHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->renderedContent = null;
        $this->drupalContent = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->renderedContent) {
            $this->renderedContent = render($this->drupalContent);
        }

        return $this->renderedContent;
    }

    /**
     * {@inheritdoc}
     */
    final public function sendContent()
    {
        echo $this->getContent();

        return $this;
    }
}
