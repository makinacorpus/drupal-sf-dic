<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Node;

/**
 * Original code this was forked from TFD7 project:
 *   https://github.com/TFD7/TFD7
 *
 * All credits to its authors.
 *
 * @author René Bakx
 * @see http://tfd7.rocks for more information
 */
class Hide extends \Twig_Node_Print
{
    /**
     * {@inheritdoc}
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$_temp=")->subcompile($this->getNode('expr'))->raw(";\n")
            ->write("echo is_array(\$_temp) ? hide(\$_temp) : '';\n")
            ->write("unset(\$_temp);\n")
        ;
    }
}
