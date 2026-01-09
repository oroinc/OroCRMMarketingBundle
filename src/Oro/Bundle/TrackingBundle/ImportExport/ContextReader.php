<?php

namespace Oro\Bundle\TrackingBundle\ImportExport;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

/**
 * Reads tracking data from import/export context for processing.
 */
class ContextReader extends AbstractReader
{
    /**
     * @var array
     */
    protected $data;

    #[\Override]
    public function read()
    {
        $data = $this->data;

        $this->data = null;

        return $data;
    }

    #[\Override]
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$context->hasOption('data')) {
            throw new InvalidConfigurationException(
                'Configuration reader must contain "data".'
            );
        } else {
            $this->data = $context->getOption('data');
        }
    }
}
