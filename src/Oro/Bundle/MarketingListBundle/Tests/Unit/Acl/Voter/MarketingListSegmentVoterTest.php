<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Acl\Voter;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Acl\Voter\MarketingListSegmentVoter;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MarketingListSegmentVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var MarketingListSegmentVoter */
    private $voter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->voter = new MarketingListSegmentVoter($this->doctrineHelper);
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testVote($attributes, $marketingList, $expected)
    {
        $object = new Segment();

        $this->voter->setClassName(Segment::class);

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        $this->assertMarketingListLoad($marketingList);

        $token = $this->createMock(TokenInterface::class);
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        $marketingList = new MarketingList();

        return [
            [['VIEW'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['CREATE'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['EDIT'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['DELETE'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['ASSIGN'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],

            [['VIEW'], $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['CREATE'], $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['EDIT'], $marketingList, MarketingListSegmentVoter::ACCESS_DENIED],
            [['DELETE'], $marketingList, MarketingListSegmentVoter::ACCESS_DENIED],
            [['ASSIGN'], $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN],
        ];
    }

    private function assertMarketingListLoad($marketingList)
    {
        $repository = $this->createMock(EntityRepository::class);

        $repository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($marketingList));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));
    }
}
