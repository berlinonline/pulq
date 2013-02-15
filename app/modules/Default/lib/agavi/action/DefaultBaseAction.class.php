<?php

use \Honeybee\Agavi\Action\BaseAction;

/**
 * The DefaultBaseAction serves as the base action to all actions implemented inside of the Default module.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Default
 * @subpackage      Agavi/Action
 */
class DefaultBaseAction extends BaseAction
{
    /*
      This is the base action all your module's actions should extend. This way,
      you can easily inject new functionality into all of this module's actions.

      One example would be to extend the getCredentials() method and return a list
      of credentials that all actions in this module require.

      Even if you don't need any of the above and this class remains empty, it is
      strongly recommended you keep it. There shall come the day where you are
      happy to have it this way ;)

      It is of course highly recommended that you change the names of any default
      base classes to carry a prefix and have an overall meaningful naming scheme.
      You can enable the usage of the respective custom template files via
      build.properties settings. Also, keep in mind that you can define templates
      for specific modules in case you require this.
     */
}
