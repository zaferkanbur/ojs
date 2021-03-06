<?php

namespace Ojs\JournalBundle\Controller;

use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ORM\QueryBuilder;
use Ojs\Common\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\Journal;
use Ojs\JournalBundle\Entity\JournalSetting;
use Ojs\AdminBundle\Form\Type\JournalType;
use Ojs\UserBundle\Entity\User;
use Ojs\JournalBundle\Entity\JournalRole;
use Ojs\WorkflowBundle\Document\JournalWorkflowStep;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Yaml\Parser;

class ManagerController extends Controller
{
    /**
     * @param  null     $journalId
     * @return Response
     */
    public function journalSettingsAction($journalId = null)
    {
        if (!$journalId) {
            $journal = $this->get("ojs.journal_service")->getSelectedJournal();
        } else {
            $em = $this->getDoctrine()->getManager();
            $journal = $em->getRepository('OjsJournalBundle:Journal')->find($journalId);
        }
        if (!$this->isGranted('EDIT', $journal)) {
            throw new AccessDeniedException($this->get('translator')->trans("You cant view this page."));
        }
        $form = $this->createForm(
            new JournalType(),
            $journal,
            array(
                'action' => $this->generateUrl('ojs_admin_journal_update', array('id' => $journal->getId())),
                'method' => 'PUT',
            )
        );

        return $this->render(
            'OjsJournalBundle:Manager:journal_settings.html.twig',
            array(
                'journal' => $journal,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param $journal
     * @param $settingName
     * @param  string             $settingValue if null, function will return current value
     * @param  bool               $encoded      set true if setting stored as json_encoded
     * @return array|mixed|string
     */
    private function updateJournalSetting($journal, $settingName, $settingValue, $encoded = false)
    {
        if (!$this->isGranted('EDIT', $journal)) {
            throw new AccessDeniedException($this->get('translator')->trans("You cant view this page."));
        }
        $em = $this->getDoctrine()->getManager();
        /** @var JournalSetting $setting */
        $setting = $em->
        getRepository('OjsJournalBundle:JournalSetting')->
        findOneBy(array('journal' => $journal, 'setting' => $settingName));

        $settingString = $encoded ? json_encode($settingValue) : $settingValue;
        if ($setting) {
            $setting->setValue($settingString);
        } else {
            $setting = new JournalSetting($settingName, $settingString, $journal);
        }
        $em->persist($setting);
        $em->flush();

        return $setting ? ($encoded ? json_decode($setting->getValue()) : $setting->getValue()) : [];
    }

    /**
     * @todo setttings enumeration should be done, otherwise setting keys will be a garbage
     * @param  Request  $req
     * @param  integer  $journalId
     * @return Response
     */
    public function journalSettingsSubmissionAction(Request $req, $journalId = null)
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $journal  Journal */
        $journal = !$journalId ?
            $this->get("ojs.journal_service")->getSelectedJournal() :
            $em->getRepository('OjsJournalBundle:Journal')->find($journalId);
        if ($req->getMethod() == 'POST') {
            if (!empty($req->get('submissionMandatoryLanguages'))) {
                $this->updateJournalSetting(
                    $journal,
                    'submissionMandatoryLanguages',
                    $req->get('submissionMandatoryLanguages'),
                    true
                );
            }
            if (!empty($req->get('submissionAbstractTemplate'))) {
                $this->updateJournalSetting(
                    $journal,
                    'submissionAbstractTemplate',
                    $req->get('submissionAbstractTemplate'),
                    false
                );
            }
            if (!empty($req->get('copyrightStatement'))) {
                $this->updateJournalSetting($journal, 'copyrightStatement', $req->get('copyrightStatement'), false);
            }
        }
        $yamlParser = new Parser();
        $root = $this->container->getParameter('kernel.root_dir');
        $data = array(
            'settings' => array(
                'submissionMandatoryLanguages' => $journal->getSetting('submissionMandatoryLanguages') ?
                    json_decode($journal->getSetting('submissionMandatoryLanguages')->getValue()) :
                    null,
                'submissionAbstractTemplate' => $journal->getSetting('submissionAbstractTemplate') ?
                    $journal->getSetting('submissionAbstractTemplate')->getValue() :
                    null,
                'copyrightStatement' => $journal->getSetting('copyrightStatement') ?
                    $journal->getSetting('copyrightStatement')->getValue() :
                    null,
            ),
            'abstractTemplates' => $yamlParser->parse(
                file_get_contents(
                    $root.
                    '/../src/Ojs/JournalBundle/Resources/data/abstracttemplates.yml'
                )
            ),
            'copyrightTemplates' => $yamlParser->parse(
                file_get_contents(
                    $root.
                    '/../src/Ojs/JournalBundle/Resources/data/copyrightTemplates.yml'
                )
            ),
            'journal' => $journal,
            'allLanguages' => $journal->getLanguages(),
        );

        return $this->render('OjsJournalBundle:Manager:journal_settings_submission.html.twig', $data);
    }

    /**
     * @param  Request      $req
     * @param  null|integer $journalId
     * @return Response
     */
    public function journalSettingsMailAction(Request $req, $journalId = null)
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $journal  Journal */
        $journal = !$journalId ?
            $this->get("ojs.journal_service")->getSelectedJournal() :
            $em->getRepository('OjsJournalBundle:Journal')->find($journalId);
        if ($req->getMethod() == 'POST' && !empty($req->get('emailSignature'))) {
            $this->updateJournalSetting($journal, 'emailSignature', $req->get('emailSignature'), false);
        }
        $emailSignature = $journal->getSetting('emailSignature') ? $journal->getSetting('emailSignature')->getValue(
        ) : null;

        return $this->render(
            'OjsJournalBundle:Manager:journal_settings_mail.html.twig',
            array(
                'journal' => $journal,
                'emailSignature' => $emailSignature,
            )
        );
    }

    /**
     *
     * @return Response
     */
    public function journalSettingsPageAction()
    {
        $journal = $this->get("ojs.journal_service")->getSelectedJournal();
        $twig = $this->get('okulbilisimcmsbundle.twig.post_extension');
        $object = $twig->encode($journal);
        $source = new Entity("Okulbilisim\\CmsBundle\\Entity\\Post");
        $ta = $source->getTableAlias();
        $source->manipulateQuery(
            function (QueryBuilder $qb) use ($ta, $journal, $object) {
                return $qb->andWhere(
                    $qb->expr()->andX(
                        $qb->expr()->eq("$ta.object", ":object"),
                        $qb->expr()->eq("$ta.objectId", ":journalId")
                    )
                )
                    ->setParameters(
                        [
                            'object' => $object,
                            'journalId' => $journal->getId(),
                        ]
                    );
            }
        );
        $grid = $this->get('grid');
        $grid->setSource($source);
        $grid->setHiddenColumns(['post_type', 'content', 'object', 'createdAt', 'updatedAt', 'deletedAt', 'objectId']);
        $grid->getColumn('title')->setSafe(false);
        $gridAction = $this->get('grid_action');

        $grid->addRowAction($gridAction->editAction('post_edit', 'id'));
        $grid->addRowAction($gridAction->deleteAction('post_delete', 'id'));

        $data = [];
        $data['grid'] = $grid;
        $data['journal'] = $journal;

        return $grid->getGridResponse('OjsJournalBundle:Manager:journal_settings_pages/list.html.twig', $data);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function userIndexAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $journal = $this->get("ojs.journal_service")->getSelectedJournal();
        $mySteps = [];
        if ($journal) {
            /** @var JournalWorkflowStep[] $allowedWorkflowSteps */
            $allowedWorkflowSteps = $dm->getRepository('OjsWorkflowBundle:JournalWorkflowStep')
                ->findBy(array('journalid' => $journal->getId()));
            // @todo we should query in a more elegant way
            // { roles : { $elemMatch : { role : "ROLE_EDITOR" }} })
            // Don't know how to query $elemMatch
            $journalService = $this->get('ojs.journal_service');
            foreach ($allowedWorkflowSteps as $step) {
                if (($journalService->hasJournalRole('ROLE_EDITOR') || $journalService->hasJournalRole(
                            'ROLE_JOURNAL_MANAGER'
                        )) || $this->checkStepAndUserRoles($step)
                ) {
                    $mySteps[] = $step;
                }
            }
        }
        $waitingTasksCount = [];
        foreach ($mySteps as $step) {
            /** @var JournalWorkflowStep $step */
            // TODO : this should be in repository
            $countQuery = $dm->getRepository('OjsWorkflowBundle:ArticleReviewStep')
                ->createQueryBuilder('ars');
            $countQuery->field('step.$id')->equals(new \MongoId($step->getId()));
            $countQuery->field('finishedDate')->equals(null);
            $waitingTasksCount[$step->getId()] = $countQuery->count()->getQuery()->execute();
        }
        // waiting invited steps
        $invitedWorkflowSteps = $dm->getRepository('OjsWorkflowBundle:Invitation')
            ->findBy(array('userId' => $user->getId(), 'accept' => null));

        if ($user->isAdmin()) {
            return $this->redirect($this->generateUrl('ojs_admin_dashboard'));
        }

        return $this->render(
            'OjsJournalBundle:User:userwelcome.html.twig',
            array(
                'mySteps' => $mySteps,
                'waitingCount' => $waitingTasksCount,
                'invitedSteps' => $invitedWorkflowSteps,
            )
        );
    }

    /**
     * @param $step
     * @return bool
     */
    private function checkStepAndUserRoles(JournalWorkflowStep $step)
    {
        $myRoles = $this->get('ojs.journal_service')->getSelectedJournalRoles();
        $stepRoles = $step->getRoles();
        foreach ($myRoles as $myRole) {
            foreach ((array) $stepRoles as $stepRole) {
                /**
                 * @var JournalRole $stepRole
                 * @var JournalRole $myRole
                 */
                if ($stepRole['role'] === $myRole) {
                    return true;
                }
            }
        }

        return false;
    }
}
