<?php

declare(strict_types=1);

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Async;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Tests\Functional\DataFixtures\LoadMarketingListData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

class UpdateMarketingListProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
    }

    public function testProcessWhenNoMarketingListsFound(): void
    {
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $isCalled = false;
        $callback = static function () use (&$isCalled) {
            $isCalled = true;
        };

        $eventDispatcher->addListener(UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT, $callback);
        self::assertFalse($isCalled);

        $sentMessage = self::sendMessage(
            MarketingListUpdateTopic::getName(),
            [MarketingListUpdateTopic::CLASS_NAME => Contact::class]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_marketing_list.async.update_marketing_list', $sentMessage);

        $eventDispatcher->removeListener(UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT, $callback);
        self::assertFalse($isCalled);
    }

    public function testProcess(): void
    {
        $this->loadFixtures([LoadMarketingListData::class]);

        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        $isCalled = false;
        $callback = static function () use (&$isCalled) {
            $isCalled = true;
        };

        $eventDispatcher->addListener(UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT, $callback);

        self::assertFalse($isCalled);

        $sentMessage = self::sendMessage(
            MarketingListUpdateTopic::getName(),
            [MarketingListUpdateTopic::CLASS_NAME => Contact::class]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_marketing_list.async.update_marketing_list', $sentMessage);

        $eventDispatcher->removeListener(UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT, $callback);
        self::assertTrue($isCalled);

        self::assertTrue(
            self::getLoggerTestHandler()->hasInfo('Marketing lists found for class. Notifying listeners.')
        );
    }
}
