<?php

namespace Ojs\ApiBundle\Controller;

use Elastica\Query;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class SearchRestController extends FOSRestController
{

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  description="search Institutions",
     *  parameters={
     * {
     *          "name"="q",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="search term"
     *      },
     *      {
     *          "name"="apikey",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="Apikey"
     *      }
     *  }
     * )
     * @Get("/search/journal/{journalId}/users")
     * @TODO elasticsearch will be better for performance. "like" query should be removed
     *
     * @param  Request $request
     * @return mixed
     */
    public function searchJournalUsersAction(Request $request)
    {
        $q = $request->get('q');
        $repo = $this->getDoctrine()->getManager()->getRepository('OjsUserBundle:User');
        $query = $repo->createQueryBuilder('u')
            ->where('u.username LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search')
            ->setParameter('search', '%'.$q.'%')
            ->getQuery();

        return $query->getResult();
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  description="search users in username-email-tags and subjects (accepts regex inputs)",
     *  parameters={
     * {
     *          "name"="q",
     *          "dataType"="string",
     *          "required"="true",
     *          "description"="search term"
     *      }
     *  }
     * )
     * @Get("/search/user")
     *
     * @param  Request $request
     * @return array
     */
    public function getUsersAction(Request $request)
    {
        $q = $request->get('q');
        $search = $this->container->get('fos_elastica.index.search.user');

        $s1 = new Query\Regexp();
        $s1->setValue('username', $q);

        $s2 = new Query\Regexp();
        $s2->setValue('subjects', $q);

        $s3 = new Query\Regexp();
        $s3->setValue('tags', $q);

        $query = new Query\Bool();
        $query->addShould($s1);
        $query->addShould($s2);
        $query->addShould($s3);

        $results = $search->search($query);
        $data = [];
        foreach ($results as $result) {
            $data[] = array_merge(array('id' => $result->getId()), $result->getData());
        }

        return $data;
    }
}
