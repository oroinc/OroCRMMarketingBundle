<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Acl\Voter;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Acl\Voter\MarketingListSegmentVoter;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MarketingListSegmentVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
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
    public function testVote(array $attributes, ?MarketingList $marketingList, int $expected)
    {
        $object = new Segment();

        $this->voter->setClassName(Segment::class);

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn(1);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->any())
            ->method('findOneBy')
            ->willReturn($marketingList);
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $token = $this->createMock(TokenInterface::class);
        $this->assertSame(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    public function attributesDataProvider(): array
    {
        $marketingList = new MarketingList();

        return [
            [['VIEW'], null, VoterInterface::ACCESS_ABSTAIN],
            [['CREATE'], null, VoterInterface::ACCESS_ABSTAIN],
            [['EDIT'], null, VoterInterface::ACCESS_ABSTAIN],
            [['DELETE'], null, VoterInterface::ACCESS_ABSTAIN],
            [['ASSIGN'], null, VoterInterface::ACCESS_ABSTAIN],

            [['VIEW'], $marketingList, VoterInterface::ACCESS_ABSTAIN],
            [['CREATE'], $marketingList, VoterInterface::ACCESS_ABSTAIN],
            [['EDIT'], $marketingList, VoterInterface::ACCESS_DENIED],
            [['DELETE'], $marketingList, VoterInterface::ACCESS_DENIED],
            [['ASSIGN'], $marketingList, VoterInterface::ACCESS_ABSTAIN],
        ];
    }
}
