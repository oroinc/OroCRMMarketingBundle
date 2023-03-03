<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\UserBundle\Entity\User;

class TrackingWebsiteTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingWebsite */
    private $website;

    protected function setUp(): void
    {
        $this->website = new TrackingWebsite();
    }

    public function testId()
    {
        $this->assertNull($this->website->getId());
    }

    public function testPrePersist()
    {
        $this->assertNull($this->website->getCreatedAt());
        $this->website->prePersist();
        $this->assertInstanceOf(\DateTime::class, $this->website->getCreatedAt());
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->website->getUpdatedAt());
        $this->website->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $this->website->getUpdatedAt());
    }

    /**
     * @dataProvider propertyProvider
     */
    public function testProperties(string $property, mixed $value, mixed $expected)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertNull(
            $propertyAccessor->getValue($this->website, $property)
        );

        $propertyAccessor->setValue($this->website, $property, $value);

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->website, $property)
        );
    }

    public function propertyProvider(): array
    {
        $date = new \DateTime();
        $user = new User();
        $organization = $this->createMock(Organization::class);

        return [
            ['name', 'test', 'test'],
            ['identifier', 'uniqid', 'uniqid'],
            ['url', 'http://example.com', 'http://example.com'],
            ['createdAt', $date, $date],
            ['updatedAt', $date, $date],
            ['owner', $user, $user],
            ['organization', $organization, $organization]
        ];
    }
}
