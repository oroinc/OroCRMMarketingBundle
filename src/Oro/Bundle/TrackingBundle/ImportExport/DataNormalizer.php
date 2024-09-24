<?php

namespace Oro\Bundle\TrackingBundle\ImportExport;

use Oro\Bundle\ImportExportBundle\Processor\EntityNameAwareInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class DataNormalizer extends ConfigurableEntityNormalizer implements EntityNameAwareInterface
{
    const DEFAULT_NAME = 'visit';

    /**
     * @var string
     */
    protected $entityName;

    /** @var array fields to correct url encoded data */
    protected $urlEncodeFields = ['name', 'title', 'userIdentifier', 'url', 'code'];

    #[\Override]
    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    #[\Override]
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return parent::denormalize(
            $this->updateData($data),
            $type,
            $format,
            $context
        );
    }

    #[\Override]
    public function normalize($object, string $format = null, array $context = [])
    {
        throw new \Exception('Not implemented');
    }

    #[\Override]
    public function supportsDenormalization($data, string $type, string $format = null, array $context = array()): bool
    {
        return is_array($data) && $type == $this->entityName;
    }

    #[\Override]
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return false;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function updateData(array $data)
    {
        $result          = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->urlEncodeFields, true)) {
                $data[$key] = urldecode($value);
            }
        }
        $result['data']  = json_encode($data);

        if (empty($data['name'])) {
            $data['name'] = self::DEFAULT_NAME;
        }
        if (!isset($data['value'])) {
            $data['value'] = 1;
        }

        $result['event'] = $data;

        if (!empty($result['event']['website'])) {
            $result['event']['website'] = [
                'identifier' => $result['event']['website']
            ];
        }

        return $result;
    }
}
