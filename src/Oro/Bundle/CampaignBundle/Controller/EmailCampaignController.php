<?php

namespace Oro\Bundle\CampaignBundle\Controller;

use Oro\Bundle\CampaignBundle\Async\Topic\SendEmailCampaignTopic;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\Handler\EmailCampaignHandler;
use Oro\Bundle\CampaignBundle\Form\Type\EmailCampaignType;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Email Campaign related controller (Create/Update/View/Send)
 *
 * @Route("/campaign/email")
 */
class EmailCampaignController extends AbstractController
{
    /**
     * @Route("/", name="oro_email_campaign_index")
     * @AclAncestor("oro_email_campaign_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => EmailCampaign::class
        ];
    }

    /**
     * Create email campaign
     *
     * @Route("/create", name="oro_email_campaign_create")
     * @Template("@OroCampaign/EmailCampaign/update.html.twig")
     * @Acl(
     *      id="oro_email_campaign_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCampaignBundle:EmailCampaign"
     * )
     */
    public function createAction(Request $request)
    {
        return $this->update(new EmailCampaign(), $request);
    }

    /**
     * Edit email campaign
     *
     * @Route("/update/{id}", name="oro_email_campaign_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_email_campaign_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCampaignBundle:EmailCampaign"
     * )
     * @param EmailCampaign $entity
     * @return array
     */
    public function updateAction(EmailCampaign $entity, Request $request)
    {
        return $this->update($entity, $request);
    }

    /**
     * View email campaign
     *
     * @Route("/view/{id}", name="oro_email_campaign_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_email_campaign_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCampaignBundle:EmailCampaign"
     * )
     * @Template
     * @param EmailCampaign $entity
     * @return array
     */
    public function viewAction(EmailCampaign $entity)
    {
        $stats = $this->getDoctrine()
            ->getRepository("OroCampaignBundle:EmailCampaignStatistics")
            ->getEmailCampaignStats($entity);

        return [
            'entity' => $entity,
            'stats' => $stats,
            'show_stats' => (bool) array_sum($stats),
            'send_allowed' => $this->isManualSendAllowed($entity)
        ];
    }

    /**
     * Process save email campaign entity
     *
     * @param EmailCampaign $entity
     * @param Request $request
     * @return array|Response
     */
    protected function update(EmailCampaign $entity, Request $request)
    {
        $factory = $this->get(FormFactoryInterface::class);
        $form = $factory->createNamed('oro_email_campaign', EmailCampaignType::class);

        $requestStack = $this->get(RequestStack::class);
        $handler = new EmailCampaignHandler($requestStack, $form, $this->getDoctrine());

        if ($handler->process($entity)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->get(TranslatorInterface::class)->trans('oro.campaign.emailcampaign.controller.saved.message')
            );

            return $this->get(Router::class)->redirect($entity);
        }

        $isUpdateOnly = $requestStack->getCurrentRequest()->get(EmailCampaignHandler::UPDATE_MARKER, false);

        // substitute submitted form with new not submitted instance to ignore validation errors
        // on form after transport field was changed
        if ($isUpdateOnly) {
            $form = $factory->createNamed('oro_email_campaign', EmailCampaignType::class, $form->getData());
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView()
        ];
    }

    /**
     * @Route("/send/{id}", name="oro_email_campaign_send", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_email_campaign_send",
     *      type="action",
     *      label="oro.campaign.acl.send_emails.label",
     *      description="oro.campaign.acl.send_emails.description",
     *      group_name="",
     *      category="marketing"
     * )
     *
     * @param EmailCampaign $emailCampaign
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendAction(EmailCampaign $emailCampaign, Request $request)
    {
        if ($this->isManualSendAllowed($emailCampaign)) {
            // Schedule email campaign sending
            $messageProducer = $this->get(MessageProducerInterface::class);
            $messageProducer->send(SendEmailCampaignTopic::getName(), ['email_campaign' => $emailCampaign->getId()]);

            // Update sent status to hide send button
            $manager = $this->getDoctrine()->getManagerForClass(EmailCampaign::class);
            $emailCampaign->setSent(true);
            $manager->persist($emailCampaign);
            $manager->flush($emailCampaign);

            $request->getSession()->getFlashBag()->add(
                'success',
                $this->get(TranslatorInterface::class)->trans('oro.campaign.emailcampaign.controller.sent')
            );
        } else {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->get(TranslatorInterface::class)->trans('oro.campaign.emailcampaign.controller.send_disallowed')
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'oro_email_campaign_view',
                ['id' => $emailCampaign->getId()]
            )
        );
    }

    /**
     * @param EmailCampaign $entity
     * @return bool
     */
    protected function isManualSendAllowed(EmailCampaign $entity)
    {
        $sendAllowed = $entity->getSchedule() === EmailCampaign::SCHEDULE_MANUAL
            && !$entity->isSent()
            && $this->isGranted('oro_email_campaign_send');

        if ($sendAllowed) {
            $transportSettings = $entity->getTransportSettings();
            if ($transportSettings) {
                $validator = $this->get(ValidatorInterface::class);
                $errors = $validator->validate($transportSettings);
                $sendAllowed = count($errors) === 0;
            }
        }

        return $sendAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                FormFactoryInterface::class,
                EmailCampaignSenderBuilder::class,
                RequestStack::class,
                Router::class,
                TranslatorInterface::class,
                ValidatorInterface::class,
                MessageProducerInterface::class
            ]
        );
    }
}
