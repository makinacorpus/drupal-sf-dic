<?php

namespace MakinaCorpus\Drupal\Sf\Twig\Extension;

/**
 * Renders Drupal blocks into Twig templates
 */
class DrupalBlockExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_block', [$this, 'doRenderBlock'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Render a Drupal block
     */
    public function doRenderBlock($module, $delta)
    {
        // $block = block_load($module, $delta);
        $blocks = module_invoke($module, 'block_info');

        if (!isset($blocks[$delta])) {
            return;
        }

        $block = (object)$blocks[$delta];
        $block->module = $module;
        $block->delta = $delta;
        $block->status = 1;
        $block->region = 'content';
        $block->weight = 0;
        $block->theme = $GLOBALS['theme_key'];

        // Those won't serve any purpose but avoid nasty PHP warnings
        $block->title = '';
        $block->pages = '';
        $block->visibility = 0; // BLOCK_VISIBILITY_NOTLISTED (block module could be disabled)
        $block->custom = null;

        $blockList = [$block];
        drupal_alter('block_list', $blockList);

        $render_array = _block_get_renderable_array(_block_render_blocks($blockList));

        return drupal_render($render_array);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_block';
    }
}
