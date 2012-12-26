<?php

/**
 * The IDatabaseSetupAction interface is responseable for setting up databases for usage.
 *
 * @copyright       BerlinOnline Stadtportal GmbH & Co. KG
 * @author          Tom Anheyer
 * @package Pulq
 * @subpackage Agavi/Database
 */
interface IDatabaseSetupAction
{    
    /**
     * 
     * init a new databas einstance 
     */
    public function actionCreate($tearDownFirst = FALSE);
    
    /**
     * 
     * drop the database on the server
     */
    public function actionDelete();
    
    /**
     * 
     * addtional job to enable the database
     */
    public function actionEnable();
}