<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\EventListener;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserData;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @dbIsolationPerTest
 */
class UpdateMarketingListOnEntityChangeTest extends WebTestCase
{
    use MessageQueueExtension;
    use ConfigManagerAwareTestTrait;

    private ConfigProvider $entityConfigProvider;
    private Cache|CacheInterface $cacheProvider;

    protected function setUp(): void
    {
        $this->initClient();

        $this->loadFixtures([LoadUserData::class]);

        $this->getOptionalListenerManager()->enableListener('oro_marketing_list.event_listener.on_entity_change');

        $this->entityConfigProvider = self::getContainer()->get('oro_entity_config.provider.entity');
        $this->cacheProvider = self::getContainer()->get('oro_marketing_list.marketing_list.cache');

        $this->addUserToContactInformationAllowedList();
    }

    public function testOnFlushFeatureDisabled(): void
    {
        $this->toggleMarketingListFeature(false);
        $this->updateUser();

        self::assertMessagesEmpty(MarketingListUpdateTopic::getName());
    }

    public function testOnFlushWithDisabledListener(): void
    {
        $this->toggleMarketingListFeature();
        $this->disableListener();
        $this->updateUser();

        self::assertMessagesEmpty(MarketingListUpdateTopic::getName());
    }

    public function testOnFlush(): void
    {
        $this->toggleMarketingListFeature();
        $this->updateUser();

        $this->assertMessagesSent(
            MarketingListUpdateTopic::getName(),
            [[MarketingListUpdateTopic::CLASS_NAME => User::class]]
        );
    }

    private function updateUser(): void
    {
        /** @var User $user */
        $user = $this->getReference(LoadUserData::SIMPLE_USER);
        $user->setEnabled(!$user->isEnabled());

        $entityManager = $this->getEntityManager(User::class);
        $entityManager->persist($user);
        $entityManager->flush();
    }

    private function toggleMarketingListFeature($enable = true): void
    {
        $configManager = self::getConfigManager();
        $configManager->set('oro_marketing_list.feature_enabled', $enable);
        self::getContainer()->get('oro_featuretoggle.checker.feature_checker')->resetCache();
    }

    private function disableListener(): void
    {
        self::getContainer()->get('oro_marketing_list.event_listener.on_entity_change')->setEnabled(false);
    }

    private function getEntityManager(string $className): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass($className);
    }

    /**
     * Add field email of entity User as contact information then the flush of entity will trigger the message
     * send to queue to update marketing list in testing. As User entity always exist under Oro platform.
     */
    protected function addUserToContactInformationAllowedList(): void
    {
        $config = $this->entityConfigProvider->getConfig(User::class, 'email');
        $config->set('contact_information', 'email');

        $this->cacheProvider->delete('oro_marketing_list.allowed_entities');
    }

    protected function tearDown(): void
    {
        $config = $this->entityConfigProvider->getConfig(User::class, 'email');
        $config->remove('contact_information');

        $this->cacheProvider->delete('oro_marketing_list.allowed_entities');
    }
}
