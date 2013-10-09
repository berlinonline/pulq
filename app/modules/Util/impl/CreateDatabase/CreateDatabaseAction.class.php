<?php

use Pulq\Util\Agavi\Action\BaseAction;
use Pulq\Agavi\Database\PulqDatabase;
use Elastica\Status;

class Util_CreateDatabaseAction extends BaseAction
{
    public function execute(AgaviRequestDataHolder $rd)
    {
        $db = $rd->getParameter('database');
        $db->setup();

        return 'Success';
    }

    public function isSecure()
    {
        return FALSE;
    }

    protected function createDb(PulqDatabase $db)
    {
        $connection = $db->getConnection();
        $index_config = $db->getParameter('index');
        $alias_name = $index_config['name'];
        $index_name = $alias_name . '_' . date('Y-m-d_H-i-s');

        $existing_indices = array();
        $status = new Status($connection);
        foreach ($status->getIndicesWithAlias( $alias_name ) as $aliased_index ) {
            $existing_indices[] = $aliased_index;
        }

        $definition_file = $index_config['definition_file'];
        $definition = json_decode(file_get_contents($definition_file), true);

        $connection->getIndex($index_name)->create($definition);

        $connection->getIndex($index_name)->addAlias($alias_name, true);

        foreach ($existing_indices as $existing_index) {
            $existing_index->delete();
        }
    }
}
