<?php

namespace Oro\Bundle\CampaignBundle\Controller;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\Handler\EmailCampaignHandler;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Serve CRUD of EmailCampaign entity.
 *
 * @Route("/campaign/email")
 */
class EmailCampaignController extends Controller
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Session */
    private $session;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Router */
    private $router;

    /** @var Form */
    private $form;

    /** @var EmailCampaignHandler */
    private $formHandler;

    /** @var EmailCampaignSenderBuilder */
    private $emailCampaignSenderBuilder;

    /**
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     * @param Session $session
     * @param FormFactoryInterface $formFactory
     * @param Router $router
     * @param Form $form
     * @param EmailCampaignHandler $formHandler
     * @param EmailCampaignSenderBuilder $emailCampaignSenderBuilder
     */
    public function __construct(
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        Session $session,
        FormFactoryInterface $formFactory,
        Router $router,
        Form $form,
        EmailCampaignHandler $formHandler,
        EmailCampaignSenderBuilder $emailCampaignSenderBuilder
    ) {
        $this->validator = $validator;
        $this->translator = $translator;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->form = $form;
        $this->formHandler = $formHandler;
        $this->emailCampaignSenderBuilder = $emailCampaignSenderBuilder;
    }

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
     * @Template("OroCampaignBundle:EmailCampaign:update.html.twig")
     * @Acl(
     *      id="oro_email_campaign_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCampaignBundle:EmailCampaign"
     * )
     */
    public function createAction()
    {
        return $this->update(new EmailCampaign());
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
    public function updateAction(EmailCampaign $entity)
    {
        return $this->update($entity);
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
     * @return array
     */
    protected function update(EmailCampaign $entity)
    {
        if ($this->formHandler->process($entity)) {
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('oro.campaign.emailcampaign.controller.saved.message')
            );

            return $this->router->redirect($entity);
        }
        $form = $this->getForm();

        return [
            'entity' => $entity,
            'form'   => $form->createView()
        ];
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    protected function getForm()
    {
        $isUpdateOnly = $this
            ->get('request_stack')
            ->getCurrentRequest()
            ->get(EmailCampaignHandler::UPDATE_MARKER, false);

        $form = $this->form;
        if ($isUpdateOnly) {
            // substitute submitted form with new not submitted instance to ignore validation errors
            // on form after transport field was changed
            $form = $this->formFactory
                ->createNamed('oro_email_campaign', 'oro_email_campaign', $form->getData());
        }

        return $form;
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
     * @param EmailCampaign $entity
     * @return RedirectResponse
     */
    public function sendAction(EmailCampaign $entity)
    {
        if ($this->isManualSendAllowed($entity)) {
            $sender = $this->emailCampaignSenderBuilder->getSender($entity);
            $sender->send();

            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('oro.campaign.emailcampaign.controller.sent')
            );
        } else {
            $this->session->getFlashBag()->add(
                'error',
                $this->translator->trans('oro.campaign.emailcampaign.controller.send_disallowed')
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'oro_email_campaign_view',
                ['id' => $entity->getId()]
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
                $errors = $this->validator->validate($transportSettings);
                $sendAllowed = count($errors) === 0;
            }
        }

        return $sendAllowed;
    }
}
