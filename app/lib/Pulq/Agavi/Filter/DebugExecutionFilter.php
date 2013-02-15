<?php

namespace Pulq\Agavi\Filter;

class DebugExecutionFilter extends \PhpDebugToolbarAgaviExecutionFilter
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
