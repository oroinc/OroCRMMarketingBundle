<?php

namespace Oro\Bundle\CampaignBundle\Form\Handler;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailCampaignHandler
{
    const UPDATE_MARKER = 'formUpdateMarker';

    /** @var RequestStack */
    protected $requestStack;

    /** @var RegistryInterface */
    protected $registry;

    /** @var FormInterface */
    protected $form;

    /**
     * @param RequestStack $requestStack
     * @param FormInterface $form
     * @param RegistryInterface $registry
     */
    public function __construct(
        RequestStack $requestStack,
        FormInterface $form,
        RegistryInterface $registry
    ) {
        $this->requestStack = $requestStack;
        $this->form = $form;
        $this->registry = $registry;
    }

    /**
     * Process form
     *
     * @param EmailCampaign $entity
     *
     * @return bool
     */
    public function process(EmailCampaign $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($request);
            if (!$request->get(self::UPDATE_MARKER, false) && $this->form->isValid()) {
                $em = $this->registry->getManagerForClass('OroCampaignBundle:EmailCampaign');
                $em->persist($entity);
                $em->flush();

                return true;
            }
        }

        return false;
    }
}
