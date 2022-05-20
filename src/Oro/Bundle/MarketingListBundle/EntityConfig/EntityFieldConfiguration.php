<?php

namespace Oro\Bundle\MarketingListBundle\EntityConfig;

use Oro\Bundle\EntityConfigBundle\EntityConfig\FieldConfigInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Provides validations field config for entity scope.
 */
class EntityFieldConfiguration implements FieldConfigInterface
{
    public function getSectionName(): string
    {
        return 'entity';
    }

    public function configure(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->scalarNode('contact_information')
                ->info('`string` enables you to change contact information (phone or email) for the entity. Each ' .
                'contact_information type requires its own template. E.g. phone => ' .
                '“OroMarketingListBundle:MarketingList/ExtendField:phone.html.twig”.')
            ->end()
            ->node('immutable', 'normalized_boolean')
                ->info('`boolean` this attribute can be used to prohibit changing the entity state (no matter ' .
                    'whether it is enabled or not) for the entity. If TRUE than the current state cannot be changed.')
                ->defaultFalse()
            ->end()
        ;
    }
}
