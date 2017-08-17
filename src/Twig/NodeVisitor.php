<?php

namespace MakinaCorpus\Drupal\Sf\Twig;

use MakinaCorpus\Drupal\Sf\Twig\Node\Hide;
use MakinaCorpus\Drupal\Sf\Twig\Node\Render;

/**
 * Original code this was forked from TFD7 project:
 *   https://github.com/TFD7/TFD7
 *
 * All credits to its authors.
 *
 * @author René Bakx
 * @see http://tfd7.rocks for more information
 */
class NodeVisitor extends \Twig_BaseNodeVisitor
{
    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(\Twig_Node $node, \Twig_Environment $env)
    {
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(\Twig_Node $node, \Twig_Environment $env)
    {
        if (!$node instanceof \Twig_Node_Print) {
            return $node;
        }

        // make sure that every {{ }} printed object is handled as using autorender)
        if (!$node->getNode('expr') instanceof \Twig_Node_Expression_Function) {
            if ($env->isAutoRender()) {
                $targetNode = $node->getNode('expr');

                if ($targetNode instanceof \Twig_Node_Expression_Name) {
                    $targetNode->setAttribute('always_defined', true);
                }

                if (!$targetNode instanceof \Twig_Node_Expression_MethodCall) {
                    if (method_exists($node, 'getLine')) {
                        // Twig 1.x
                        $node = new Render($targetNode, $node->getLine(), $node->getNodeTag());
                    } else {
                        // Twig 2.x
                        $node = new Render($targetNode, $node->getTemplateLine(), $node->getNodeTag());
                    }
                }
            }
        } else if ($node->getNode('expr') instanceof \Twig_Node_Expression_Function) {
            $targetNode = $node->getNode('expr');

            if ('hide' === $targetNode->getAttribute('name') && $targetNode instanceof \Twig_Node_Expression_MethodCall) {
                $targetNode->setAttribute('always_defined', true);

                if (method_exists($node, 'getLine')) {
                    // Twig 1.x
                    $node = new Hide($targetNode, $node->getLine(), $node->getNodeTag());
                } else {
                    // Twig 2.x
                    $node = new Hide($targetNode, $node->getTemplateLine(), $node->getNodeTag());
                }
            }
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 10;
    }
}
