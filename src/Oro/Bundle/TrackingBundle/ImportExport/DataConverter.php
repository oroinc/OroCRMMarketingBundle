<?php

namespace Oro\Bundle\TrackingBundle\ImportExport;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class DataConverter extends AbstractTableDataConverter
{
    #[\Override]
    protected function getHeaderConversionRules()
    {
        return [
            'e_n'         => 'name',
            'e_v'         => 'value',
            'action_name' => 'title',
            'idsite'      => 'website',
            '_uid'        => 'userIdentifier',
            '_rcn'        => 'code',
        ];
    }

    #[\Override]
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
