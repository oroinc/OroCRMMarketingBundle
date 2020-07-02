<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class RemoveContactInformation extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Remove contact_information info from field configs which type does not supports contact information.';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $type = Type::getType(Types::ARRAY);
        $platform = $this->connection->getDatabasePlatform();

        $query = 'SELECT id, data FROM oro_entity_config_field WHERE type NOT IN (?,?)';
        $params = ['string', 'text'];

        $this->logQuery($logger, $query, $params);
        $fields = $this->connection->fetchAll($query, $params);

        try {
            $this->connection->beginTransaction();
            foreach ($fields as $field) {
                $data = $type->convertToPHPValue($field['data'], $platform);
                if (isset($data['entity']['contact_information'])) {
                    unset($data['entity']['contact_information']);

                    $data = $type->convertToDatabaseValue($data, $platform);

                    $query = 'UPDATE oro_entity_config_field SET data = ? WHERE id = ?';
                    $params = [$data, $field['id']];

                    $this->logQuery($logger, $query, $params);
                    $this->connection->executeQuery($query, $params);
                }
            }
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
