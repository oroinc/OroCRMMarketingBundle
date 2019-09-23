<?php

namespace Oro\Bundle\TrackingBundle\Controller;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TrackingBundle\Entity\TrackingData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Provides create action for the TrackingData entity.
 *
 * @Route("/tracking/data")
 */
class TrackingDataController extends AbstractController
{
    /**
     * @Route("/create", name="oro_tracking_data_create")
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $jobResult = $this->getJobExecutor()->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            'import_request_to_database',
            [
                ProcessorRegistry::TYPE_IMPORT => [
                    'entityName'     => TrackingData::class,
                    'processorAlias' => 'oro_tracking.processor.data',
                    'data'           => $request->query->all(),
                ]
            ]
        );

        $isSuccessful = $jobResult->isSuccessful();
        $response     = [];

        if (!$isSuccessful) {
            $response['errors'] = $jobResult->getFailureExceptions();
        }

        $validationErrors = $jobResult->getContext()->getErrors();
        if ($validationErrors) {
            $isSuccessful = false;

            $response['validation'] = $validationErrors;
        }

        $response['success'] = $isSuccessful;

        return new JsonResponse($response, $isSuccessful ? 201 : 400);
    }

    /**
     * @return JobExecutor
     */
    protected function getJobExecutor()
    {
        return $this->container->get(JobExecutor::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            JobExecutor::class,
        ];
    }
}
