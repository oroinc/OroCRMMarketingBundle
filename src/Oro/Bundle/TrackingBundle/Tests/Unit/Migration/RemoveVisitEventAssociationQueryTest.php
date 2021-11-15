<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Migration;

use Oro\Bundle\TrackingBundle\Migration\RemoveVisitEventAssociationQuery;

class RemoveVisitEventAssociationQueryTest extends \PHPUnit\Framework\TestCase
{
    public function testInitialized()
    {
        $query = new RemoveVisitEventAssociationQuery('Some\Entity', 'some_table', true);
        self::assertEquals(
            'Remove association relation from Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent entity '
            . 'to Some\Entity (association kind: association, relation type: manyToOne, '
            . 'drop relation column/table: yes, source table: oro_tracking_visit_event, target table: some_table).',
            $query->getDescription()
        );
    }
}
