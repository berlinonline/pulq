<?php

abstract class Image extends BaseDataObject
{
    protected $src;
    protected $width;
    protected $height;

    public function getArrayScopes()
    {
        return array(
            'list' => array(
                'src',
                'width',
                'height'
            ),
            'detail' => array(
                'src',
                'width',
                'height'
            )
        );
    }
}
