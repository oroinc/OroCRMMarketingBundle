<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Tests\Functional\DataFixtures\LoadEmailTemplateData;

class LoadEmailTemplateEntityContactData extends AbstractFixture implements DependentFixtureInterface
{
    public const NOT_SYSTEM_ENTITY_NAME_CONTACT = 'emailTemplate9';
    public const SYSTEM_ENTITY_NAME_CONTACT = 'emailTemplate10';

    private const OWNER_USER_REFERENCE = 'simple_user';

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [LoadEmailTemplateData::class];
    }

    public function load(ObjectManager $manager): void
    {
        $owner = $this->getReference(self::OWNER_USER_REFERENCE);

        $emailTemplate9 = new EmailTemplate('not_system_entity_name_contact', 'test Application etc');
        $emailTemplate9->setIsSystem(false);
        $emailTemplate9->setEntityName(Contact::class);
        $emailTemplate9->setOrganization($owner->getOrganization());

        $emailTemplate10 = new EmailTemplate('system_entity_name_contact', 'test Application etc');
        $emailTemplate10->setIsSystem(true);
        $emailTemplate10->setEntityName(Contact::class);
        $emailTemplate10->setOrganization($owner->getOrganization());

        $manager->persist($emailTemplate9);
        $manager->persist($emailTemplate10);
        $manager->flush();

        $this->setReference(self::NOT_SYSTEM_ENTITY_NAME_CONTACT, $emailTemplate9);
        $this->setReference(self::SYSTEM_ENTITY_NAME_CONTACT, $emailTemplate10);
    }
}
