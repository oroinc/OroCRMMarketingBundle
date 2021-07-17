<?php

namespace Oro\Bundle\TrackingBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TrackingWebsiteHandler
{
    use RequestHandlerTrait;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ObjectManager
     */
    protected $manager;

    public function __construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  TrackingWebsite $entity
     * @return bool True on successfull processing, false otherwise
     */
    public function process(TrackingWebsite $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);

            if ($this->form->isValid()) {
                $this->manager->persist($entity);
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
