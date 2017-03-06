<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Stub;

class EntityStub
{
    /** @var mixed */
    protected $id;

    /**
     * EntityStub constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
