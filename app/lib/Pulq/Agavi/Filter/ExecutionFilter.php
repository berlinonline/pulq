<?php

namespace Pulq\Agavi\Filter;

/**
 * The ExecutionFilter class registers view executions for the ResourceFilter.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 */
class ExecutionFilter extends \AgaviExecutionFilter
{
    protected function executeView(\AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);

        ResourceFilter::addModule(
            $container->getViewModuleName(), 
            $container->getOutputType()->getName()
        );

        return $viewResult;
    }
}
