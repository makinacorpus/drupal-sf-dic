<?php

namespace Drupal\Core\Breadcrumb;

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a breadcrumb manager.
 *
 * Can be assigned any number of BreadcrumbBuilderInterface objects by calling
 * the addBuilder() method. When build() is called it iterates over the objects
 * in priority order and uses the first one that returns TRUE from
 * BreadcrumbBuilderInterface::applies() to build the breadcrumbs.
 *
 * @see \Drupal\Core\DependencyInjection\Compiler\RegisterBreadcrumbBuilderPass
 *
 * @rewritten
 */
class BreadcrumbManager
{
    /**
     * @var BreadcrumbBuilderInterface[]
     */
    private $builders = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $builders = [])
    {
        foreach ($builders as $builder) {
            if (!$builder instanceof BreadcrumbBuilderInterface) {
                throw new \InvalidArgumentException(sprintf("builder should be an instance of %s, %d given", BreadcrumbBuilderInterface::class, gettype($builder)));
            }
        }

        $this->builders = $builders;
    }

    /**
     * {@inheritdoc}
     */
    public function applies(RouteMatchInterface $route_match)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RouteMatchInterface $route_match)
    {
        foreach ($this->builders as $builder) {

            if (!$builder->applies($route_match)) {
              continue;
            }

            $breadcrumb = $builder->build($route_match);

            if ($breadcrumb instanceof Breadcrumb) {
                break;
            } else {
                throw new \UnexpectedValueException('Invalid breadcrumb returned by ' . get_class($builder) . '::build().');
            }
        }

        return isset($breadcrumb) ? $breadcrumb : new Breadcrumb();
    }
}
