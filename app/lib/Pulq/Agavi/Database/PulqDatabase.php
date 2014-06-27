<?php

namespace Pulq\Agavi\Database;

use \AgaviDatabase;

abstract class PulqDatabase extends AgaviDatabase
{
    abstract public function setup();
}
