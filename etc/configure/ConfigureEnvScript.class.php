<?php

/**
 * ConfigureEnvScript wraps the usage of the {@see EnvironmentConfigurator} class.
 * This class basically is responseable for checking the passed cli arguments
 * and invoke the correct methods on it's {@see EnvironmentConfigurator}.
 *
 * @package    BerlinOnline
 * @subpackage Configure
 *
 * @author     Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @copyright  BerlinOnline GmbH & Co. KG
 *
 * @version $Id: EnvironmentConfigurator.class.php 4182 2011-06-08 12:22:07Z tschmitt $
 */
class ConfigureEnvScript
{
    // ---------------------------------- <CONSTANTS> ----------------------------------------------
    //
    // The below constants are used to name the possible commands,
    // that can be passed to this script via the cli interface.
    //
    // -----------------------------------------------------------

    /**
     * Holds the name of the init action.
     */
    const ACTION_INIT = 'init';

    /**
     * The prefix used to pass commands to this script on the cli.
     */
    const ARG_PREFIX = '--';

    /**
     * Name of the file argument that can be provided to our import hosts action.
     */
    const ARG_NAME_HOSTS_FILE = 'file';

    // ---------------------------------- </CONSTANTS> ----------------------------------------------

    // ----------------------------------- <MEMBERS> ------------------------------------------------

    /**
     * Holds the list of currently supported portals.
     *
     * @todo Move to config file?
     *
     * @var array<string>
     */
    protected static $supported_actions = array(
        ConfigureEnvScript::ACTION_INIT
    );

    /**
     * The EnvironmentConfigurator that we use to perform our user requested operations.
     *
     * @var EnvironmentConfigurator
     */
    protected $env_configurator;

    // ----------------------------------- </MEMBERS> ------------------------------------------------

    /**
     * Create new ConfigureEnvScript instance thereby initializing our EnvironmentConfigurator
     * and actions.
     */
    public function __construct()
    {
        $this->env_configurator = new EnvironmentConfigurator();
    }

    // ----------------------------------- <PUBLIC INTERFACE> ------------------------------------------------

    /**
     * Takes an array with (cli) arguments in order to findout and perform
     * the requested action on our aggregated {@see EnvironmentConfigurator}.
     *
     * @param array $cli_arguments
     */
    public function run(array $cli_arguments)
    {
        $action = $this->whichAction($cli_arguments);

        if (null === $action)
        {
            $this->printHelp();
            return;
        }

        $action_method = 'run' . ucfirst($action);

        if (is_callable(array($this, $action_method)))
        {
            $this->$action_method($cli_arguments);
        }
    }

    // ----------------------------------- </PUBLIC INTERFACE> -----------------------------------------------


    // ------------------------------------ <WORKING METHODS> ------------------------------------------------

    /**
     * Find out if we are passed a valid action from the cli
     * and return it, else return null.
     *
     * @param array $cli_arguments
     *
     * @return string One of the above {@see self::ACTION_*} constants.
     */
    protected function whichAction(array $cli_arguments)
    {
        $action = null;

        if (1 < count($cli_arguments))
        {
            $action = str_replace(
                ConfigureEnvScript::ARG_PREFIX,
                '',
                $cli_arguments[1]
            );
        }

        return (in_array($action, self::$supported_actions)) ? $action : null;
    }

    /**
     * Run the init action on our environment configurator.
     *
     * @param array $cli_arguments
     */
    protected function runInit(array $cli_arguments = array())
    {
        $this->printInitMsg();
        $this->env_configurator->initializeMainConfig();
    }

    // ------------------------------------ <WORKING METHODS> ------------------------------------------------


    // --------------------------------- <INFO SCREENS> ----------------------------------------------

    /**
     * Prints a message used to start off the initialize config action.
     */
    protected function printInitMsg()
    {
        print(
<<<MSG

#--------------------------------------------------------------------------------------------------------#
#                                       Configure Environment                                            #
#--------------------------------------------------------------------------------------------------------#
# Following up, you will be prompted for some important environment information,                         #
# that we need to get the system up and running for you.                                                 #
# If you provide an invalid value, you will be prompted as long as we are not satisfied by your answer.  #
# If you don't know the answer, just abort the process and ask one of the guys from the cm-project team. #
#--------------------------------------------------------------------------------------------------------#

MSG
        );
    }

    /**
     * Prints a help/usage message.
     */
    protected function printHelp()
    {
        print(
<<<HELP
#--------------------------------------------------------------------------------------------------------#
#                                       Configure Environment                                            #
#--------------------------------------------------------------------------------------------------------#

Usage: ./configure-env {command}

Avaiable Commands
   --init   Initialize the main config for your current environment.

HELP
        );
    }

    // --------------------------------- </INFO SCREENS> ---------------------------------------------
}
