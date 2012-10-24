<?php

require_once('PHP/PMD/Rule/Naming/ShortVariable.php');
require_once('PHP/PMD/Rule/IClassAware.php');

class PulqShortnamesPmdRule extends PHP_PMD_Rule_Naming_ShortVariable
{
    protected $allowedShortVariabes = array(
        'rd',
        'rq',
        'ro',
        'ns',
        'rc',
        'to',
        'tm',
        'id'
    );

    protected function doCheckNodeImage(PHP_PMD_AbstractNode $node)
    {
        $image = $node->getImage();
        $variableName = strpos($image, '$') === 0 ? substr($image, 1) : $image;
        if (in_array($variableName, $this->allowedShortVariabes))
        {
            return;
        }

        parent::doCheckNodeImage($node);
    }

}
