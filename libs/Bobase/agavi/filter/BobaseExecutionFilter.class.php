<?php

/**
 * The BobaseExecutionFilter class registers view executions for the BobaseScriptFilter.
 *
 * @version         $Id: BobaseExecutionFilter.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Bobase
 * @subpackage      Agavi/Filter
 */
class BobaseExecutionFilter extends AgaviExecutionFilter
{
    protected function executeView(AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);
        $outputType = $container->getOutputType()->getName();
        BobaseScriptFilter::addView(
            $container->getViewModuleName(),
            $container->getActionName(),
            $container->getViewName(),
            $outputType
        );
        return $viewResult;
    }

}

?>