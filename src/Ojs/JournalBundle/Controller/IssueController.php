<?php

namespace Ojs\JournalBundle\Controller;

use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ODM\MongoDB\DocumentManager;
use Ojs\Common\Helper\ActionHelper;
use Symfony\Component\HttpFoundation\Request;
use Ojs\Common\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\Issue;
use Ojs\JournalBundle\Form\IssueType;

/**
 * Issue controller.
 *
 */
class IssueController extends Controller
{
    /**
     * Lists all Issue entities.
     *
     */
    public function indexAction()
    {
        $source = new Entity('OjsJournalBundle:Issue');
        $grid = $this->get('grid')->setSource($source);

        $actionColumn = new ActionsColumn("actions", 'actions');
        $rowAction[] = ActionHelper::showAction('issue_show', 'id');
        $rowAction[] = ActionHelper::editAction('issue_edit', 'id');
        $rowAction[] = ActionHelper::deleteAction('issue_delete', 'id');

        $actionColumn->setRowActions($rowAction);
        $grid->addColumn($actionColumn);
        $data = [];
        $data['grid'] = $grid;
        return $grid->getGridResponse('OjsJournalBundle:Issue:index.html.twig',$data);
    }

    /**
     * Creates a new Issue entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Issue();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $header = $request->request->get('header');
            $cover = $request->request->get('cover');
            if($header){
                $entity->setHeaderOptions(json_encode($header));
            }
            if($cover){
                $entity->setCoverOptions(json_encode($cover));
            }

            $em->persist($entity);
            $em->flush();
            $this->successFlashBag('successful.create');
            return $this->redirectToRoute('issue_show', ['id' => $entity->getId()]);
        }

        return $this->render('OjsJournalBundle:Issue:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Issue entity.
     *
     * @param Issue $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Issue $entity)
    {
        $user = $this->getUser();
        $form = $this->createForm(new IssueType(), $entity, array(
            'action' => $this->generateUrl('issue_create'),
            'method' => 'POST',
            'user' => $user
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Issue entity.
     *
     */
    public function newAction()
    {
        $entity = new Issue();
        $form = $this->createCreateForm($entity);

        return $this->render('OjsJournalBundle:Issue:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Issue entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsJournalBundle:Issue')->find($id);
        $this->throw404IfNotFound($entity);

        return $this->render('OjsJournalBundle:Issue:show.html.twig', array(
                    'entity' => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsJournalBundle:Issue')->find($id);
        $this->throw404IfNotFound($entity);
        $editForm = $this->createEditForm($entity);

        return $this->render('OjsJournalBundle:Issue:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Issue entity.
     * @param  Issue                        $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Issue $entity)
    {
        $user = $this->getUser();
        $form = $this->createForm(new IssueType(), $entity, array(
            'action' => $this->generateUrl('issue_update', array('id' => $entity->getId())),
            'method' => 'POST',
            'user'=>$user
        ));

        return $form;
    }

    /**
     * Edits an existing Issue entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsJournalBundle:Issue')->find($id);
        $this->throw404IfNotFound($entity);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $header = $request->request->get('header');
            $cover = $request->request->get('cover');
            if($header){
                $entity->setHeaderOptions(json_encode($header));
            }
            if($cover){
                $entity->setCoverOptions(json_encode($cover));
            }
            $em->persist($entity);
            $em->flush();
            $this->successFlashBag('successful.update');
            return $this->redirectToRoute('issue_edit', ['id' => $id]);
        }

        return $this->render('OjsJournalBundle:Issue:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Issue entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsJournalBundle:Issue')->find($id);
        $this->throw404IfNotFound($entity);
        $em->remove($entity);
        $em->flush();
        $this->successFlashBag('successful.remove');
        return $this->redirectToRoute('issue');
    }

}
