<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\CampaignBundle\Async\Topic\SendEmailCampaignTopic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SendEmailCampaignTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new SendEmailCampaignTopic();
    }

    public function validBodyDataProvider(): array
    {
        $requiredOptionsSet = [
            'email_campaign' => 1,
        ];

        return [
            'only required options' => [
                'body' => $requiredOptionsSet,
                'expectedBody' => $requiredOptionsSet,
            ],
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' =>
                    '/The required option "email_campaign" is missing./',
            ],
            'wrong email_campaign type' => [
                'body' => [
                    'email_campaign' => '1',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "email_campaign" with value "1" is expected to be of type "int"/',
            ],
        ];
    }
}
