<?php

declare(strict_types=1);

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class MarketingListUpdateTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new MarketingListUpdateTopic();
    }

    public function validBodyDataProvider(): array
    {
        return [
            'required only' => [
                'body' => [MarketingListUpdateTopic::CLASS_NAME => \stdClass::class],
                'expectedBody' => [MarketingListUpdateTopic::CLASS_NAME => \stdClass::class],
            ],
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' => '/The required option "class" is missing./',
            ],
            'class has invalid type' => [
                'body' => [MarketingListUpdateTopic::CLASS_NAME => new \stdClass()],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "class" with value stdClass is expected '
                    . 'to be of type "string"/',
            ],
        ];
    }
}
