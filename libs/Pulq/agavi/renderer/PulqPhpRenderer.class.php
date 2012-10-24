<?php

class PulqPhpRenderer extends AgaviPhpRenderer
{
    protected $macroRecursionDepth = 0;
    protected $currentMacroTemplatePath;


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

    protected function macro($macroName, array $parameters = array())
    {
        $templateDirs = $this->getParameter('template_dirs', array());

        $found = false;

        foreach($templateDirs as $dir)
        {
            $macroFileName = $macroName . $this->getDefaultExtension();
            $templatePath = $dir . DIRECTORY_SEPARATOR . $macroFileName;
            if (is_readable($templatePath))
            {
                $this->currentMacroTemplatePath = $templatePath;
                $found = true;
                break;
            }
        }

        if ($found)
        {
            $this->macroRecursionDepth++;

            if ($this->macroRecursionDepth > $this->getParameter('max_recursion_depth', 3))
            {
                throw new MacroRecursionException("Too many macro recusions in macro '$macroName': " . $this->macroRecursionDepth);
            }

            //Clean up the local scope to prevent the macro template from doing something stupid ...
            unset($macroName);
            unset($templateDirs);
            unset($templatePath);
            unset($dir);
            unset($found);
            unset($macroFileName);

            extract($parameters);
            require($this->currentMacroTemplatePath);
            $this->macroRecursionDepth--;
        }
        else
        {
            // Oh noez! I NO CAN HAS MACRO FILE?! I better throw up ...
            $paths = implode(PATH_SEPARATOR, $templateDirs);
            throw new MacroFileNotFoundException("Macro file '$macroFileName' not found in '$paths'.");
        
        }

    }
}
