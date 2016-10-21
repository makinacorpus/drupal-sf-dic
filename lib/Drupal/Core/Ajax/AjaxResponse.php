<?php

/**
 * @file
 * Contains \Drupal\Core\Ajax\AjaxResponse.
 */

namespace Drupal\Core\Ajax;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * JSON response object for AJAX requests.
 *
 * @ingroup ajax
 */
class AjaxResponse extends JsonResponse
{
    /**
     * @var array
     */
    protected $commands = [];

    /**
     * Should the current request return JSONP instead of JSON
     *
     * @return boolean
     */
    protected function isJSONP()
    {
        return !empty($_POST['ajax_iframe_upload']);
    }

    /**
     * Add an AJAX command to the response.
     *
     * @param \Drupal\Core\Ajax\CommandInterface|string|array $command
     *   An AJAX command object implementing CommandInterface.
     *   If a string is passed, consider it's a class name and intanciate it
     * @param bool $prepend
     *   A boolean which determines whether the new command should be executed
     *   before previously added commands. Defaults to FALSE.
     *
     * @return AjaxResponse
     *   The current AjaxResponse.
     */
    public function addCommand($command, $prepend = false)
    {
        if ($command instanceof CommandInterface) {
            $command = $command->render();
        }

        if ($prepend) {
            array_unshift($this->commands, $command);
        } else {
            $this->commands[] = $command;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sendHeaders()
    {
        if ($this->isJSONP()) {
            $contentType = 'text/html; charset=utf-8';
        } else {
            $contentType = 'application/json; charset=utf-8';
        }

        $this->headers->add([
            'X-Drupal-Ajax-Token' => '1',
            'Content-Type' => $contentType,
        ]);

        parent::sendHeaders();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sendContent()
    {
        // Print the response.
        $commands = ajax_prepare_response([
            '#type' => 'ajax',
            '#commands' => $this->commands,
        ]);

        $output = ajax_render($commands);

        if ($this->isJSONP()) {
            echo '<textarea>' . $output . '</textarea>';
        } else {
            echo $output;
        }

        ajax_footer();

        return $this;
    }
}
