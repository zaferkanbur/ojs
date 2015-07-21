<?php

namespace Ojs\SiteBundle\Controller;

use Ojs\AdminBundle\Form\Type\InstitutionApplicationType;
use Ojs\AdminBundle\Form\Type\JournalApplicationType;
use Ojs\Common\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\Institution;
use Ojs\JournalBundle\Entity\Journal;
use Ojs\JournalBundle\Entity\JournalContact;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application controller.
 *
 */
class ApplicationController extends Controller
{
    /**
     * @param  Request $request
     * @return RedirectResponse|Response
     */
    public function journalAction(Request $request)
    {

        $application = new Journal();
        $form = $this->createForm(new JournalApplicationType(), $application);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $application->setStatus(0);
                $em->persist($application);
                $em->flush();

                return $this->redirect($this->get('router')->generate('ojs_apply_journal_success'));
            }

            $session = $this->get('session');
            $session->getFlashBag()->add('error', $this->get('translator')
                ->trans('An error has occured. Please check the form and resubmit.'));
            $session->save();
        }

        return $this->render('OjsSiteBundle:Application:journal.html.twig', array('form' => $form->createView()));
    }

    public function institutionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $application = new Institution();

        $form = $this->createForm(new InstitutionApplicationType(), $application);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($application);
                $em->flush();

                return $this->redirect($this->get('router')->generate('ojs_apply_institute_success'));
            }

            $session = $this->get('session');
            $session->getFlashBag()->add('error',$this->get('translator')
                ->trans('An error has occured. Please check form and resubmit.'));
        }

        return $this->render('OjsSiteBundle:Application:institution.html.twig', array('form' => $form->createView()));
    }

    public function instituteSuccessAction()
    {
        return $this->render('OjsSiteBundle:Application:institution_success.html.twig');
    }

    public function journalSuccessAction()
    {
        return $this->render('OjsSiteBundle:Application:journal_success.html.twig');
    }
}
