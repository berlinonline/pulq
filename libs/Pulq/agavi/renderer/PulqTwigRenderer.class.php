<?php

class PulqTwigRenderer extends AgaviTwigRenderer
{
	/**
	 * Render the presentation and return the result.
	 *
	 * @param      AgaviTemplateLayer The template layer to render.
	 * @param      array              The template variables.
	 * @param      array              The slots.
	 * @param      array              Associative array of additional assigns.
	 *
	 * @return     string A rendered result.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      1.0.6
	 */
	public function render(AgaviTemplateLayer $layer, array &$attributes = array(), array &$slots = array(), array &$moreAssigns = array())
    {
        $moduleName = $layer->getParameter('module');
        $additionalTemplatePath = sprintf(AgaviConfig::get('core.app_dir').'/modules/%s/templates', $moduleName);

        if (is_dir($additionalTemplatePath))
        {
            $this->setParameter('template_dirs', array_merge(
                array(
                    $additionalTemplatePath
                ),
                (array)$this->getParameter('template_dirs', array())
            ));
        }

        return parent::render($layer, $attributes, $slots, $moreAssigns);
    }
}
