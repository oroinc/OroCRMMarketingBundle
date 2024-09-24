<?php

namespace Oro\Bundle\CampaignBundle\Controller\Api\Rest;

use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller for EmailTemplate entity.
 */
class EmailTemplateController extends RestController
{
    /**
     * REST GET email campaign templates by entity name
     *
     * @ApiDoc(
     *     description="Get email campaign templates by entity name",
     *     resource=true
     * )
     */
    #[AclAncestor('oro_email_emailtemplate_index')]
    public function cgetAction(
        int $id = null,
        bool $includeNonEntity = true,
        bool $includeSystemTemplates = false
    ): Response {
        if (!$id) {
            return $this->handleView(
                $this->view(null, Response::HTTP_NOT_FOUND)
            );
        }

        $marketingList = $this->container->get('doctrine')
            ->getRepository(MarketingList::class)
            ->find((int)$id);

        $organization = $this->container->get('oro_security.token_accessor')->getOrganization();

        if (!$marketingList || !$organization) {
            return $this->handleView(
                $this->view(null, Response::HTTP_NOT_FOUND)
            );
        }

        $templatesQb = $this->container->get('doctrine')
            ->getRepository(EmailTemplate::class)
            ->getEntityTemplatesQueryBuilder(
                $marketingList->getEntity(),
                $organization,
                $includeNonEntity,
                $includeSystemTemplates
            );

        $templates = $templatesQb->getQuery()->getArrayResult();
        return $this->handleView(
            $this->view($templates, Response::HTTP_OK)
        );
    }

    #[\Override]
    public function getManager()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    #[\Override]
    public function getForm()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    #[\Override]
    public function getFormHandler()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            ['doctrine' => ManagerRegistry::class]
        );
    }
}
