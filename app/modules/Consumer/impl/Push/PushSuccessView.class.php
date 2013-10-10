<?php

use Pulq\Consumer\Agavi\View\BaseView;
use Symfony\Component\Console\Output\ConsoleOutput;

class Consumer_Push_PushSuccessView extends BaseView 
{
    public function executeJson(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $output = array(
            'status' => array(
                'success' => true,
                'errors' => array()
            )
        );
        return json_encode($output);
    }

    public function throwOutputTypeNotImplementedException()
    {
        die('foo');
    }
}
