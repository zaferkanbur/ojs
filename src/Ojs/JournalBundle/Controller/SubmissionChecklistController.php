<?php

namespace Ojs\JournalBundle\Controller;

use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Ojs\Common\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\SubmissionChecklist;
use Ojs\JournalBundle\Form\SubmissionChecklistType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * SubmissionChecklist controller.
 *
 */
class SubmissionChecklistController extends Controller
{

    /**
     * Lists all SubmissionChecklist entities.
     *
     */
    public function indexAction()
    {
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('VIEW', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        $source = new Entity('OjsJournalBundle:SubmissionChecklist');
        if ($journal) {
            $ta = $source->getTableAlias();
            $source->manipulateQuery(function (QueryBuilder $qb) use ($journal, $ta) {
                $qb->andWhere(
                    $qb->expr()->eq("$ta.journal_id", ':journal')
                )
                    ->setParameter('journal', $journal->getId());
            });
        }
        if (!$journal) {
            throw new NotFoundHttpException("Journal not found!");
        }
        $grid = $this->get('grid')->setSource($source);
        $gridAction = $this->get('grid_action');

        $actionColumn = new ActionsColumn("actions", 'actions');

        $rowAction[] = $gridAction->showAction('manager_submission_checklist_show', 'id');
        $rowAction[] = $gridAction->editAction('manager_submission_checklist_edit', 'id');
        $rowAction[] = $gridAction->deleteAction('manager_submission_checklist_delete', 'id');

        $actionColumn->setRowActions($rowAction);
        $grid->addColumn($actionColumn);
        $data = [];
        $data['grid'] = $grid;
        $data['journal_id'] = $journal;

        return $grid->getGridResponse('OjsJournalBundle:SubmissionChecklist:index.html.twig', $data);
    }

    /**
     * Creates a new SubmissionChecklist entity.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('CREATE', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        $entity = new SubmissionChecklist();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setJournal($journal);
            $entity->setJournalId($journal->getId());
            $em->persist($entity);
            $em->flush();
            $this->successFlashBag('successful.create');

            return $this->redirect($this->generateUrl('manager_submission_checklist_show', array('id' => $entity->getId())));
        }

        return $this->render('OjsJournalBundle:SubmissionChecklist:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a SubmissionChecklist entity.
     *
     * @param SubmissionChecklist $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SubmissionChecklist $entity)
    {
        $languages = [];
        if(is_array($this->container->getParameter('languages'))) {
            foreach ($this->container->getParameter('languages') as $key => $language) {
                if(array_key_exists('code', $language)) {
                    $languages[$language['code']] = $language['name'];
                }
            }
        }
        $form = $this->createForm(new SubmissionChecklistType(), $entity, array(
            'action' => $this->generateUrl('manager_submission_checklist_create'),
            'languages' => $languages,
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new SubmissionChecklist entity.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('CREATE', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        $entity = new SubmissionChecklist();
        $form = $this->createCreateForm($entity);
        return $this->render('OjsJournalBundle:SubmissionChecklist:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SubmissionChecklist entity.
     *
     * @param SubmissionChecklist $entity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(SubmissionChecklist $entity)
    {
        $this->throw404IfNotFound($entity);
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('VIEW', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        return $this->render('OjsJournalBundle:SubmissionChecklist:show.html.twig', array(
            'entity' => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing SubmissionChecklist entity.
     *
     * @param SubmissionChecklist $entity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(SubmissionChecklist $entity)
    {
        $this->throw404IfNotFound($entity);
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('EDIT', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        $editForm = $this->createEditForm($entity);
        return $this->render('OjsJournalBundle:SubmissionChecklist:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a SubmissionChecklist entity.
     *
     * @param SubmissionChecklist $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(SubmissionChecklist $entity)
    {
        $languages = [];
        if(is_array($this->container->getParameter('languages'))) {
            foreach ($this->container->getParameter('languages') as $key => $language) {
                if(array_key_exists('code', $language)) {
                    $languages[$language['code']] = $language['name'];
                }
            }
        }

        $form = $this->createForm(new SubmissionChecklistType(), $entity, array(
            'action' => $this->generateUrl('manager_submission_checklist_update', array('id' => $entity->getId())),
            'languages' => $languages,
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing SubmissionChecklist entity.
     *
     * @param Request $request
     * @param SubmissionChecklist $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, SubmissionChecklist $entity)
    {
        $this->throw404IfNotFound($entity);
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('EDIT', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            $this->successFlashBag('successful.update');
            return $this->redirect($this->generateUrl('manager_submission_checklist_edit', array('id' => $entity->getId())));
        }
        return $this->render('OjsJournalBundle:SubmissionChecklist:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a SubmissionChecklist entity.
     *
     * @param Request $request
     * @param SubmissionChecklist $entity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, SubmissionChecklist $entity)
    {
        $this->throw404IfNotFound($entity);
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        if(!$this->isGranted('DELETE', $journal, 'checklist')) {
            throw new AccessDeniedException("You are not authorized for view this page!");
        }
        $em = $this->getDoctrine()->getManager();
        $csrf = $this->get('security.csrf.token_manager');
        $token = $csrf->getToken('manager_submission_checklist'.$entity->getId());
        if($token!=$request->get('_token'))
            throw new TokenNotFoundException("Token Not Found!");

        $em->remove($entity);
        $em->flush();
        $this->successFlashBag('successful.remove');

        return $this->redirectToRoute('manager_submission_checklist');
    }
}
