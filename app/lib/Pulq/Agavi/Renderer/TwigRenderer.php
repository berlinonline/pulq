<?php
namespace Pulq\Agavi\Renderer;

/**
 * Renderer with support for Twig extensions
 *
 * @author     Igor Pellegrini <igor.pellegrini@berlinonline.de>
 */
class TwigRenderer extends \AgaviTwigRenderer
{
	protected function getEngine()
	{
		if(!$this->twig) {
			$this->twig = $this->createEngineInstance();
			
			// register extensions
			$this->twig->addExtension(new Twig\Extension\PulqTwigExtension());

			// assigns can be set as globals
			foreach($this->assigns as $key => $getter) {
				$this->twig->addGlobal($key, $this->context->$getter());
			}
		}

		return $this->twig;
	}
}
