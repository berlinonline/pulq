<?php

namespace Pulq\Agavi\Database\CouchDb;

use IDatabaseSetup;

/**
 * Provides couchdb database connection handle.
 *
 * @author tay
 */
class Database extends \AgaviDatabase
{
    const DEFAULT_SETUP = 'Pulq\\Agavi\\Database\\CouchDb\\DatabaseSetup';

    /**
     * our database access handle instance
     *
     * @var Client
     */
    protected $connection;

    /**
     * uses parameter 'url' for connection the couch database
     *
     * @see AgaviDatabase::connect()
     */
    protected function connect()
    {
        $couchUri = $this->getParameter('url', Client::DEFAULT_URL);

        if (! $this->hasParameter('database'))
        {
            throw new \AgaviDatabaseException(
                "Database name required but missing in given configuration."
            );
        }

        try
        {
            $this->connection = new Client(
                $couchUri,
                $this->getParameter('database', NULL),
                $this->getParameter('options', NULL)
            );
        }
        catch (ClientException $e)
        {
            throw new \AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
        }

        $this->initConnection($this->getParameter('database'));
    }

    /**
     * (non-PHPdoc)
     * @see AgaviDatabase::shutdown()
     */
    public function shutdown()
    {
        $this->connection = NULL;
    }

    /**
     * uses parameter 'database' for setup a default database and the parameter 'setup' to initialize the
     * freshly generated database
     *
     * @throws AgaviDatabaseException
     */
    protected function initConnection($databaseName)
    {
        $this->login();

        $this->resource = $databaseName;
        if (FALSE === $this->connection->getDatabase($databaseName))
        {
            try
            {
                $this->connection->createDatabase($databaseName);
                $this->resource = $databaseName;
            }
            catch (ClientException $e)
            {
                throw new \AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
            }

            if (TRUE === $this->getParameter('setup', FALSE))
            {
                $this->setupDatabase();
            }
        }
    }

    /**
     * uses parameters 'user' and 'password' for user authentification
     *
     * @throws AgaviDatabaseException
     */
    protected function login()
    {
        if ($this->hasParameter('user') && $this->hasParameter('password'))
        {
            try
            {
                $status = $this->connection->login($this->getParameter('user'), $this->getParameter('password'));
                if (TRUE !== $status)
                {
                    throw new \AgaviDatabaseException($status);
                }
            }
            catch (ClientException $e)
            {
                throw new \AgaviDatabaseException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * prepare database for use using the class defined in the parameter 'setup'
     *
     * The setup class must implement the interface IDatabaseSetup
     *
     * @see IDatabaseSetup
     * @throws AgaviDatabaseException
     */
    protected function setupDatabase()
    {
        $setupImplementor = $this->getParameter('setup_class', self::DEFAULT_SETUP);
        if (! class_exists($setupImplementor))
        {
            throw new \AgaviDatabaseException("Setup class does not exists: $setupImplementor");
        }

        $setup = new $setupImplementor();
        if ($setup instanceof IDatabaseSetup)
        {
            $setup->execute($this);
        }
        else
        {
            throw new \AgaviDatabaseException(
                "Setup class does not implement IDatabaseSetup: $setupImplementor"
            );
        }
    }
}
