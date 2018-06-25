<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Acl\Voter\MarketingListSegmentVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MarketingListSegmentVoterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MarketingListSegmentVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new MarketingListSegmentVoter($this->doctrineHelper);
    }

    protected function tearDown()
    {
        unset($this->voter);
        unset($this->doctrineHelper);
    }

    /**
     * @dataProvider attributesDataProvider
     * @param array $attributes
     * @param $marketingList
     * @param $expected
     */
    public function testVote($attributes, $marketingList, $expected)
    {
        $object = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityClass')
            ->with($object)
            ->will($this->returnValue('\stdClass'));

        $this->voter->setClassName('\stdClass');

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        $this->assertMarketingListLoad($marketingList);

        /** @var TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
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
        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

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

    /**
     * @param $marketingList
     */
    protected function assertMarketingListLoad($marketingList)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($marketingList));

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));
    }
}
