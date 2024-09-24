<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Psr\Log\LoggerInterface;

class PreserveTrackingConfigQuery extends ParametrizedMigrationQuery
{
    #[\Override]
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        if (!$this->isConfigDefault($logger)) {
            return;
        }

        $query = $this->createPreserveQuery();
        if ($dryRun) {
            $logs = $query->getDescription();
            foreach ($logs as $log) {
                $logger->info($log);
            }
        } else {
            $query->execute($logger);
        }
    }

    /**
     * @return bool
     */
    protected function isConfigDefault(LoggerInterface $logger)
    {
        $query = <<<'SQL'
SELECT COUNT(1)
FROM oro_config_value cv
JOIN oro_config c ON cv.config_id = c.id
WHERE cv.name = :name AND cv.section = :section AND c.entity = :entity
SQL;

        $params = [
            'entity' => 'app',
            'name' => 'dynamic_tracking_enabled',
            'section' => 'oro_tracking',
        ];

        $this->logQuery($logger, $query, $params);

        return !$this->connection->executeQuery($query, $params)->fetchOne();
    }

    /**
     * @return ParametrizedSqlMigrationQuery
     */
    public function createPreserveQuery()
    {
        $sql = <<<'SQL'
INSERT INTO oro_config_value
    (config_id, name, section, text_value, object_value, array_value, type, created_at, updated_at)
SELECT
    c.id,
    :name,
    :section,
    :text_value,
    :object_value,
    :array_value,
    :type,
    :created_at,
    :created_at
FROM oro_config c
WHERE c.entity = :entity
SQL;

        $query = new ParametrizedSqlMigrationQuery(
            $sql,
            [
                'entity' => 'app',
                'name' => 'dynamic_tracking_enabled',
                'section' => 'oro_tracking',
                'text_value' => '1',
                'object_value' => null,
                'array_value' => null,
                'type' => 'scalar',
                'created_at' => (new \DateTime())->setTimezone(new \DateTimeZone('UTC')),
            ],
            [
                'entity' => Types::STRING,
                'name' => Types::STRING,
                'section' => Types::STRING,
                'text_value' => Types::TEXT,
                'object_value' => Types::OBJECT,
                'array_value' => Types::ARRAY,
                'type' => Types::STRING,
                'created_at' => Types::DATETIME_MUTABLE,
            ]
        );

        $query->setConnection($this->connection);

        return $query;
    }
}
