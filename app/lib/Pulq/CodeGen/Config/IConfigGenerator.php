<?php

namespace Pulq\CodeGen\Config;

interface IConfigGenerator
{
    public function generate($name, array $affectedPaths);
}
