<?php

/**
 * The PulqExecutionFilter class registers view executions for the PulqResourceFilter.
 *
 * @version         $Id: PulqExecutionFilter.class.php 1010 2012-03-02 20:08:23Z tschmitt $
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Pulq
 * @subpackage      Agavi/Filter
 */
class PulqExecutionFilter extends AgaviExecutionFilter
{
    protected function executeView(AgaviExecutionContainer $container)
    {
        $viewResult = parent::executeView($container);
        $outputType = $container->getOutputType()->getName();
        PulqResourceFilter::addModule(
            $container->getViewModuleName(),
            $outputType
        );
        return $viewResult;
    }

}

?>
