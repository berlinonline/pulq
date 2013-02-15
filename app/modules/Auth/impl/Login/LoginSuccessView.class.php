<?php

/**
 * The Auth_Login_LoginSuccessView class handles success data presentation
 * for our various supported output types.
 *
 * @version         $Id$
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Thorsten Schmitt-Rink <tschmittrink@gmail.com>
 * @package         Auth
 * @subpackage      Mvc
 */
class Auth_Login_LoginSuccessView extends AuthBaseView
{
    public function executeBinary(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $this->executeHtml($parameters);
    }

    /**
     * Execute any html related presentation logic and sets up our template attributes.
     *
     * @param       AgaviRequestDataHolder $parameters
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function executeHtml(AgaviRequestDataHolder $parameters) // @codingStandardsIgnoreEnd
    {
        $user = $this->getContext()->getUser();

        if ($user->hasAttribute('redirect', 'de.berlinonline.contentworker.login'))
        {
            $target = $user->removeAttribute('redirect', 'de.berlinonline.contentworker.login');
        }
        else
        {
            $target = $this->getContext()->getRouting()->gen('index');
        }

        $this->getResponse()->setRedirect($target);
    }

    /**
     * Prepares and sets our json data on our webresponse.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeJson(AgaviRequestDataHolder $parameters)
    {
        if (NULL != ($container = $this->attemptForward($parameters)))
        {
            return $container;
        }

        $this->getContainer()->getResponse()->setContent(
            json_encode(
                array(
                    'result' => 'success',
                    'token' => session_id()
                )
            )
        );
    }

    /**
     * Prepares and sets our json data on our console response.
     *
     * @param       AgaviRequestDataHolder $parameters
     */
    public function executeText(AgaviRequestDataHolder $parameters)
    {
        if (NULL != ($container = $this->attemptForward($parameters)))
        {
            return $container;
        }

        $this->getContainer()->getResponse()->setContent(
            'The authentication completed successfully. The session token is: ' . "\n" . session_id() . "\n"
        );
    }

    /**
     * Create a forward container for the that was intentionally called before the login was executed.
     *
     * @return      AgaviExecutionContainer A new execution container instance,
	 *                                      fully initialized.
	 *
	 * @see         AgaviExecutionContainer::createExecutionContainer()
     */
    protected function attemptForward()
    {
        $request = $this->getContext()->getRequest();
        $requestedModule = $request->getAttribute('requested_module', 'org.agavi.controller.forwards.login');
        $requestedAction = $request->getAttribute('requested_action', 'org.agavi.controller.forwards.login');

        $container = NULL;

        if (!empty($requestedModule) && !empty($requestedAction))
        {
            $container = $this->createForwardContainer($requestedModule, $requestedAction, NULL, NULL, 'read');
        }

        return $container;
    }

}

?>