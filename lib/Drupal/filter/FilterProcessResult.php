<?php

namespace Drupal\filter;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class FilterProcessResult
{
    /**
     * @var string
     */
    protected $processedText;

    /**
     * Default constructor
     *
     * @param string $processed_text
     */
    public function __construct($processedText)
    {
        $this->setProcessedText($processedText);
    }

    /**
     * Gets the processed text
     *
     * @return string
     */
    public function getProcessedText()
    {
        return $this->processedText;
    }

    /**
     * Gets the processed text
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getProcessedText();
    }

    /**
     * Sets the processed text
     *
     * @param string $processedText
     *
     * @return $this
     */
    public function setProcessedText($processedText)
    {
        $this->processedText = $processedText;

        return $this;
    }
}
