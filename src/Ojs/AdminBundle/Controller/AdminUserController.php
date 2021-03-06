<?php

namespace Ojs\AdminBundle\Controller;

use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Source\Entity;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Ojs\Common\Controller\OjsController as Controller;
use Ojs\UserBundle\Entity\User;
use Ojs\UserBundle\Entity\UserRepository;
use Ojs\AdminBundle\Form\Type\UserType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Yaml;
use Doctrine\ORM\Query;

/**
 * User administration controller
 */
class AdminUserController extends Controller
{

    /**
     * Lists all User entities.
     * @return Response
     */
    public function indexAction()
    {
        if (!$this->isGranted('VIEW', new User())) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }

        $source = new Entity("OjsUserBundle:User");
        $source->addHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $grid = $this->get('grid');
        $gridAction = $this->get('grid_action');
        $grid->setSource($source);

        $actionColumn = new ActionsColumn("actions", 'actions');
        $rowAction[] = $gridAction->switchUserAction('ojs_public_index', ['username']);
        $rowAction[] = $gridAction->showAction('ojs_admin_user_show', 'id');
        $rowAction[] = $gridAction->editAction('ojs_admin_user_edit', 'id');
        $rowAction[] = $gridAction->userBanAction();
        $rowAction[] = $gridAction->deleteAction('ojs_admin_user_delete', 'id');

        $actionColumn->setRowActions($rowAction);
        $grid->addColumn($actionColumn);

        return $grid->getGridResponse('OjsAdminBundle:AdminUser:index.html.twig', ['grid' => $grid]);
    }

    /**
     * Creates a new User entity.
     *
     * @param  Request                   $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        if (!$this->isGranted('CREATE', new User())) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($entity);
            $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setPassword($password);
            $entity->setAvatar($request->get('user_avatar'));
            $em->persist($entity);
            $em->flush();

            $this->successFlashBag('successful.create');
            return $this->redirectToRoute(
                'ojs_admin_user_show',
                [
                    'id' => $entity->getId(),
                ]
            );
        }

        return $this->render(
            'OjsAdminBundle:AdminUser:new.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @return Response
     */
    public function newAction()
    {
        if (!$this->isGranted('CREATE', new User())) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }
        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render(
            'OjsAdminBundle:AdminUser:new.html.twig',
            array(
                'entity' => $entity,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Finds and displays a User entity.
     *
     * @param $id
     * @return Response
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsUserBundle:User')->find($id);

        if (!$this->isGranted('VIEW', $entity))
            throw new AccessDeniedException("You are not authorized for this page!");

        $this->throw404IfNotFound($entity);

        $token = $this
            ->get('security.csrf.token_manager')
            ->refreshToken('ojs_admin_user'.$entity->getId());

        return $this->render(
            'OjsAdminBundle:AdminUser:show.html.twig',
            ['entity' => $entity, 'token' => $token]
        );
    }

    /**
     * @param  bool     $username
     * @return Response
     */
    public function profileAction($username = false)
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository('OjsUserBundle:User');
        $sessionUser = $this->getUser();
        /** @var User $user */
        $user = $username ?
            $userRepo->findOneBy(array('username' => $username)) :
            $sessionUser;
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsUserBundle:User')->find($user->getId());
        $this->throw404IfNotFound($entity);

        $check = $this->getDoctrine()->getRepository('OjsUserBundle:Proxy')->findBy(
            array('proxyUserId' => $user->getId(), 'clientUserId' => $sessionUser->getId())
        );

        return $this->render(
            'OjsUserBundle:User:profile.html.twig',
            array(
                'entity' => $entity,
                'delete_form' => array(),
                'me' => ($sessionUser == $user),
                'isProxy' => (bool) $check,
            )
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @param $id
     * @return Response
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OjsUserBundle:User')->find($id);
        $this->throw404IfNotFound($entity);
        if (!$this->isGranted('EDIT', $entity)) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }

        $editForm = $this->createEditForm($entity);

        return $this->render(
            'OjsAdminBundle:AdminUser:edit.html.twig',
            array(
                'entity' => $entity,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Creates a form to edit a User entity.
     * @param  User                         $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(
            new UserType(),
            $entity,
            array(
                'action' => $this->generateUrl('ojs_admin_user_update', array('id' => $entity->getId())),
                'method' => 'PUT'
            )
        );

        return $form;
    }

    /**
     * Edits an existing User entity.
     *
     * @param  Request                   $request
     * @param $id
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $entity */
        $entity = $em->getRepository('OjsUserBundle:User')->find($id);
        $this->throw404IfNotFound($entity);
        if (!$this->isGranted('EDIT', $entity)) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }
        $oldPassword = $entity->getPassword();
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        /** @var DocumentManager $dm */
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        if ($editForm->isValid()) {
            $password = $entity->getPassword();
            if (empty($password)) {
                $entity->setPassword($oldPassword);
            } else {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($entity);
                $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
                $entity->setPassword($password);
            }

            $avatar = $request->request->get('avatar');
            $ir = $dm->getRepository('OjsSiteBundle:ImageOptions');
            $imageOptions = $ir->init($avatar, $entity, 'avatar');
            $dm->persist($imageOptions);
            $dm->flush();

            $em->flush();

            $this->successFlashBag('successful.update');

            return $this->redirectToRoute('ojs_admin_user_edit', ['id' => $id]);
        }

        return $this->render(
            'OjsAdminBundle:AdminUser:edit.html.twig',
            array(
                'entity' => $entity,
                'edit_form' => $editForm->createView(),
            )
        );
    }

    /**
     * Deletes a User entity.
     *
     * @param  Request          $request
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $entity */
        $entity = $em->getRepository('OjsUserBundle:User')->find($id);
        if (!$this->isGranted('DELETE', $entity)) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }
        if (!$entity) {
            throw $this->createNotFoundException($this->get('translator')->trans('notFound'));
        }

        $csrf = $this->get('security.csrf.token_manager');
        $token = $csrf->getToken('ojs_admin_user'.$id);
        if ($token != $request->get('_token')) {
            throw new TokenNotFoundException("Token Not Found!");
        }

        $entity->setStatus(-1);
        $em->remove($entity);
        $em->flush();

        $this->successFlashBag('successful.remove');

        return $this->redirectToRoute('ojs_admin_user_index');
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function blockAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->find('OjsUserBundle:User', $id);
        if (!$this->isGranted('EDIT', $user)) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }
        if (!$user) {
            throw new NotFoundResourceException("User not found.");
        }
        $user->setIsActive(false);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('ojs_admin_user_index'));
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws ORMException
     */
    public function unblockAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->find('OjsUserBundle:User', $id);
        if (!$this->isGranted('EDIT', $user)) {
            throw new AccessDeniedException("You are not authorized for this page!");
        }
        if (!$user) {
            throw new NotFoundResourceException("User not found.");
        }
        $user->setIsActive(true);
        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('ojs_admin_user_unblock'));
    }

    /**
     * @param  Request                   $request
     * @param  User                      $user
     * @return RedirectResponse|Response
     */
    public function sendMailAction(Request $request, User $user)
    {
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();
        $em = $this->getDoctrine()->getManager();
        $data = [];
        $serializer = $this->get('serializer');
        $session = $this->get('session');
        if ($request->isMethod('POST')) {
            $mailData = $request->get('mail');
            $mailer = $this->get('mailer');
            $message = $mailer->createMessage()
                ->setFrom($this->container->getParameter('system_email'))
                ->setTo($user->getEmail())
                ->setSubject($mailData['subject'])
                ->setBody($mailData['body'])
                ->setContentType('text/html');
            $mailer->send($message);
            $session->getFlashBag()->add('success', $this->get('translator')->trans('Email sending succefully.'));
            $session->save();

            return $this->redirect(
                $this->get('router')->generate('ujr_show_users_ofjournal', ['journal_id' => $journal->getId()])
            );
        }
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb->select('t')
            ->from('OjsJournalBundle:MailTemplate', 't')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->isNull('t.journalId'),
                    $qb->expr()->eq('t.journalId', ':journal')
                )
            )
            ->setParameter('journal', $journal->getId());
        $templates = $qb->getQuery()->getResult();
        $data['templates'] = $templates;
        $data['user'] = $user;
        $data['parameters'] = $request->query->all();
        array_walk(
            $data['parameters'],
            function (&$val) {
                $val = urldecode($val);
            }
        );

        $data['templateVars'] = json_encode(
            array_merge(
                array(
                    'journal' => json_decode($serializer->serialize($journal, 'json')),
                    'user' => json_decode($serializer->serialize($this->getUser(), 'json')),
                ),
                $data['parameters']
            )
        );

        $yamlParser = new Yaml\Parser();
        $defaultTemplates = $yamlParser->parse(
            file_get_contents(
                $this->container->getParameter('kernel.root_dir').
                '/../src/Ojs/JournalBundle/Resources/data/mailtemplates.yml'
            )
        );
        $tplKey = $request->get('template');
        $data['selectedTemplate'] = $tplKey ? (isset($defaultTemplates[$tplKey]) ? json_encode(
            $defaultTemplates[$tplKey]
        ) : null) : null;

        return $this->render('OjsJournalBundle:JournalRole:send_mail.html.twig', $data);
    }

    /**
     * Creates a form to create a User entity.
     * @param  User $entity The entity
     * @return Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(
            new UserType(),
            $entity,
            array(
                'action' => $this->generateUrl('ojs_admin_user_create'),
                'method' => 'POST'
            )
        );

        return $form;
    }
}
