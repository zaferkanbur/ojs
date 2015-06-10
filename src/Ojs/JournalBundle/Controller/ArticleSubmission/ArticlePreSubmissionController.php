<?php

namespace Ojs\JournalBundle\Controller\ArticleSubmission;

use Ojs\Common\Controller\OjsController as Controller;
use Ojs\JournalBundle\Document\ArticleSubmissionProgress;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ArticlePreSubmissionController
 * @package Ojs\JournalBundle\Controller\ArticleSubmission
 */
class ArticlePreSubmissionController extends Controller
{

    /**
     * @return Response
     */
    public function widgetAction()
    {
        $data = [];
        $journal = $this->get("ojs.journal_service")->getSelectedJournal();
        $data['journal'] = $journal;

        return $this->render('OjsJournalBundle:ArticleSubmission:preSubmission.html.twig', $data);
    }

    /**
     * @param  Request      $request
     * @param $locale
     * @return JsonResponse
     */
    public function saveAction(Request $request, $locale)
    {
        $submissionId = $request->get('submissionId', null);
        // save submission data to mongodb for resume action
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (null === $submissionId) {
            $articleSubmission = new ArticleSubmissionProgress();
        } else {
            $articleSubmission = $dm->getRepository('OjsJournalBundle:ArticleSubmissionProgress')->find($submissionId);
            if (!$articleSubmission) {
                throw $this->createNotFoundException('No submission found');
            }
            if (!$this->isGranted('EDIT', $articleSubmission)) {
                throw $this->createAccessDeniedException("Access Denied");
            }
        }
        $articleSubmission->setUserId($this->getUser()->getId());
        $articleSubmission->setStartedDate(new \DateTime());
        $articleSubmission->setLastResumeDate(new \DateTime());
        $articleSubmission->setChecklist(json_encode($request->get('checklistItems')));
        $articleSubmission->setCompetingOfInterest($request->get('competingOfInterest'));
        $dm->persist($articleSubmission);
        $dm->flush();

        return new JsonResponse(
            [
                'submissionId' => $articleSubmission->getId(),
                'locale' => $locale,
            ]
        );
    }
}
