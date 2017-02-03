<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

use Drupal\node\NodeInterface;

/**
 * Provides a thin compatibility layer for when the security component is not
 * enabled.
 */
class SecurityDowngradeExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_granted', [$this, 'isGranted']),
        ];
    }

    /**
     * Is current user granted with
     *
     * @param string|string[] $attributes
     * @param mixed $subject
     *
     * @return boolean
     */
    public function isGranted($attributes, $subject = null)
    {
        if (is_array($attributes)) {
            trigger_error("when using the downgraded security twig extension, attributes as array is no supported");
            return false;
        }

        if (null !== $subject && 'permission' !== $subject) {

            // Provide a fallback for nodes
            if ($subject instanceof NodeInterface) {
                return node_access($attributes, $subject);
            }

            // For all advanced use cases, we have no voters sorry.
            trigger_error("when using the downgraded security twig extension, subject must be null or a node");
            return false;
        }

        return user_access($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_security_downgrade';
    }
}
