<?php

/**
 * The ProjectExecutionFilter class registers view executions for the ProjectScriptFilter.
 *
 * @version         $Id: ProjectExecutionFilter.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Project
 * @subpackage      Agavi/Filter
 */
class ProjectExecutionFilter extends AgaviExecutionFilter
{
    protected function executeView(AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);
        $outputType = $container->getOutputType()->getName();
        ProjectScriptFilter::addView(
            $container->getViewModuleName(),
            $container->getActionName(),
            $container->getViewName(),
            $outputType
        );
        return $viewResult;
    }

}

?>