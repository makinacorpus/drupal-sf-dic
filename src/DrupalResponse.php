<?php

namespace MakinaCorpus\Drupal\Sf;

use Symfony\Component\HttpFoundation\Response;

/**
 * Very specific response that will convert Drupal render arrays on send()
 */
class DrupalResponse extends Response
{
    private $drupalContent;
    private $renderedContent;

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
            $this->renderedContent = drupal_render($this->drupalContent);
        }

        return $this->renderedContent;
    }

    /**
     * {@inheritdoc}
     */
    public function sendContent()
    {
        echo $this->getContent();

        return $this;
    }
}
