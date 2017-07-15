<?php

namespace MakinaCorpus\Drupal\Sf\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Yields the GET or POST value if no value has been given.
 */
final class RequestQueryValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if ($argument->isVariadic()) {
            return false;
        }

        $type = $argument->getType();

        if ($type) {
            switch ($type) {

                case 'bool':
                case 'float':
                case 'int':
                case 'string':
                    break;

                default:
                    return false;
            }
        }

        $name = $argument->getName();

        return $request->query->has($name) || $request->request->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $type = $argument->getType();
        $value = $request->get($argument->getName());

        if ($type) {
            switch ($type) {

                case 'bool':
                    yield (bool)$value;
                    break;

                case 'float':
                    yield (float)$value;
                    break;

                case 'int':
                    yield (int)$value;
                    break;

                case 'string':
                    yield (string)$value;
                    break;
            }
        } else {
            yield (string)$value;
        }
    }
}
