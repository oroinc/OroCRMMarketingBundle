<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Migration;

use Oro\Bundle\TrackingBundle\Migration\RemoveVisitIdentifierAssociationQuery;

class RemoveVisitIdentifierAssociationQueryTest extends \PHPUnit\Framework\TestCase
{
    public function testInitialized()
    {
        $query = new RemoveVisitIdentifierAssociationQuery('Some\Entity', 'some_table', true);
        self::assertEquals(
            'Remove association relation from Oro\Bundle\TrackingBundle\Entity\TrackingVisit entity to Some\Entity '
            . '(association kind: identifier, relation type: manyToOne, drop relation column/table: yes, '
            . 'source table: oro_tracking_visit, target table: some_table).',
            $query->getDescription()
        );
    }
}
