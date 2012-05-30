<?php

/**
 * The HeroExecutionFilter class registers view executions for the HeroResourceFilter.
 *
 * @version         $Id: HeroExecutionFilter.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Hero
 * @subpackage      Agavi/Filter
 */
class HeroExecutionFilter extends AgaviExecutionFilter
{
    protected function executeView(AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);
        $outputType = $container->getOutputType()->getName();
        HeroResourceFilter::addModule(
            $container->getViewModuleName(),
            $outputType
        );
        return $viewResult;
    }

}

?>
