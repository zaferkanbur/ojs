<?php

namespace Ojs\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ojs\AnalyticsBundle\Document\ObjectDownloads;
use Ojs\AnalyticsBundle\Document\ObjectViews;
use Symfony\Component\HttpFoundation\Request;

class AnalyticsRestController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Increment object view count",
     *  requirements={
     *      {
     *          "name"="page_url",
     *          "dataType"="string",
     *          "description"="Requested page url"
     *      }
     *  }
     * )
     * @Put("/analytics/view/{entity}/{id}")
     *
     * @param  Request     $request
     * @param $id
     * @param $entity
     * @return ObjectViews
     */
    public function putObjectViewAction(Request $request, $id, $entity)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $stat = new ObjectViews();
        $stat->setPageUrl($request->get("page_url"));
        $stat->setIpAddress($request->getClientIp());
        $stat->setLogDate(new \DateTime("now"));
        $stat->setObjectId($id);
        $stat->setEntity($entity);
        $dm->persist($stat);
        $dm->flush();

        return $stat;
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get object total Views"
     * )
     * @Get("/analytics/view/{entity}/{id}/")
     *
     * @param $id
     * @param $entity
     * @return mixed
     */
    public function getObjectViewAction($id, $entity)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $data = $dm->getRepository('OjsAnalyticsBundle:ObjectView')
            ->findBy(['objectId' => $id, 'entity' => $entity]);

        return $data;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Increment object download count"
     * )
     * @Put("/analytics/download/{entity}/{id}/")
     *
     * @param  Request         $request
     * @param $id
     * @param $entity
     * @return ObjectDownloads
     */
    public function putObjectDownloadAction(Request $request, $id, $entity)
    {
        $filePath = $request->get('file_path');
        $fileSize = $request->get('file_size') ? $request->get('file_size') : 0;
        $dm = $this->get('doctrine_mongodb')->getManager();
        $objectDownload = new ObjectDownloads();

        $objectDownload->setEntity($entity);
        $objectDownload->setFilePath($filePath);
        $objectDownload->setIpAddress($request->getClientIp());
        $objectDownload->setLogDate(new \DateTime("now"));
        $objectDownload->setObjectId($id);
        $objectDownload->setTransferSize($fileSize);
        $dm->persist($objectDownload);
        $dm->flush();

        return $objectDownload;
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get object total download count"
     * )
     * @Get("/analytics/download/{entity}/{id}/")
     *
     * @param  Request                                              $request
     * @param $id
     * @param $entity
     * @return array|\Ojs\AnalyticsBundle\Document\ObjectDownload[]
     */
    public function getObjectDownloadAction(Request $request, $id, $entity)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $filePath = $request->get('file_path');
        $data = $dm->getRepository('OjsAnalyticsBundle:ObjectDownload')
            ->findBy(
                [
                    'objectId' => (int) $id, // very interesting {.-.} {-|-} {'-'}
                    'entity' => $entity,
                    'filePath' => $filePath,
                ]
            );

        return $data;
    }
}
