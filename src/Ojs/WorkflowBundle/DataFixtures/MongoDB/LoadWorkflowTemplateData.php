<?php

namespace Ojs\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use \Ojs\WorkflowBundle\Document\JournalWorkflowTemplateStep;
use \Ojs\WorkflowBundle\Document\JournalWorkflowTemplate;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * @todo
 * Create workflow templates
 */
class LoadWorkflowTemplateData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $dm)
    {
        $em = $this->container->get('doctrine')->getManager();
        $journal = $em->createQuery('SELECT c FROM OjsJournalBundle:Journal c')
                        ->setMaxResults(1)->getResult();
        if (!isset($journal)) {
            return;
        }
        // load 3 base template
        $this->insertFirstTemplate($dm);
        $this->insertSecondTemplate($dm);
        $this->insertThirdTemplate($dm);
    }

    /**
     * 
     * @param mixed $dm doctrine mongodb manager
     */
    private function insertFirstTemplate($dm)
    {

        $roleRepo = $this->container->get('doctrine')->getRepository('OjsUserBundle:Role');
        $roleEditor = $roleRepo->findOneByRole('ROLE_EDITOR');
        $roleAuthor = $roleRepo->findOneByRole('ROLE_AUTHOR');
        $roleJournalManager = $roleRepo->findOneByRole('ROLE_JOURNAL_MANAGER');
        $serializer = $this->container->get('serializer');

        $step1 = new JournalWorkflowTemplateStep();
        $step1->setFirststep(true);
        $step1->setStatus('Editor is reviewing');
        $step1->setTitle('Editor Review');
        $step1->setRoles(array(
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));

        $step2 = new JournalWorkflowTemplateStep();
        $step2->setStatus('Author is updating');
        $step2->setTitle('Author Revision');
        $step2->setRoles(array(
            json_decode($serializer->serialize($roleAuthor, 'json')),
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));

        $step3 = new JournalWorkflowTemplateStep();
        $step3->setLaststep(true);
        $step3->setStatus('Ready to publish');
        $step3->setTitle('Publish');
        $step3->setRoles(array(
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));

        $dm->persist($step1);
        $dm->persist($step2);
        $dm->persist($step3);
        $step1->setNextsteps(
                array(
                    array('id' => $step2->getId(), 'title' => $step2->gettitle()),
                    array('id' => $step3->getId(), 'title' => $step3->gettitle())
                )
        );
        $step2->setNextsteps(
                array(
                    array('id' => $step1->getId(), 'title' => $step1->gettitle())
                )
        );

        $step3->setNextsteps(
                array(
                    array('id' => $step1->getId(), 'title' => $step1->gettitle()),
                    array('id' => $step2->getId(), 'title' => $step2->gettitle())
                )
        );

        $dm->persist($step1);
        $dm->persist($step2);
        $dm->persist($step3);

        $dm->flush();

        $template = new JournalWorkflowTemplate();
        $template->setTitle('One Step Review');
        $template->setFirstNode($step1);

        $dm->persist($template);
        $dm->flush();
        return $template;
    }

    /**
     * @TODO
     * @param type $dm
     */
    private function insertSecondTemplate($dm)
    {
        $roleRepo = $this->container->get('doctrine')->getRepository('OjsUserBundle:Role');
        $roleEditor = $roleRepo->findOneByRole('ROLE_EDITOR');
        $roleAuthor = $roleRepo->findOneByRole('ROLE_AUTHOR');
        $roleJournalManager = $roleRepo->findOneByRole('ROLE_JOURNAL_MANAGER');
        $roleReviewer = $roleRepo->findOneByRole('ROLE_REVIEWER');
        $serializer = $this->container->get('serializer');

        $step1 = new JournalWorkflowTemplateStep();
        $step1->setFirststep(true);
        $step1->setStatus('Editor is reviewing');
        $step1->setTitle('Editor Review');
        $step1->setRoles(array(
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));

        $step2 = new JournalWorkflowTemplateStep();
        $step2->setStatus('Author is updating');
        $step2->setTitle('Author Revision');
        $step2->setRoles(array(
            json_decode($serializer->serialize($roleAuthor, 'json')),
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));


        $step3 = new JournalWorkflowTemplateStep();
        $step3->setOnlyreply(true);
        $step3->setStatus('In Review');
        $step3->setTitle('Review');
        $step3->setRoles(array(
            json_decode($serializer->serialize($roleReviewer, 'json')),
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));


        $step4 = new JournalWorkflowTemplateStep();
        $step4->setLaststep(true);
        $step4->setStatus('Ready to publish');
        $step4->setTitle('Publish');
        $step4->setRoles(array(
            json_decode($serializer->serialize($roleEditor, 'json')),
            json_decode($serializer->serialize($roleJournalManager, 'json'))
        ));

        $dm->persist($step1);
        $dm->persist($step2);
        $dm->persist($step3);
        $dm->persist($step4);

        $step1->setNextsteps(
                array(
                    array('id' => $step2->getId(), 'title' => $step2->gettitle()),
                    array('id' => $step3->getId(), 'title' => $step3->gettitle()),
                    array('id' => $step4->getId(), 'title' => $step4->gettitle())
                )
        );
        $step2->setNextsteps(
                array(
                    array('id' => $step1->getId(), 'title' => $step1->gettitle())
                )
        );

        $step4->setNextsteps(
                array(
                    array('id' => $step1->getId(), 'title' => $step1->gettitle()),
                    array('id' => $step2->getId(), 'title' => $step2->gettitle())
                )
        );

        $dm->persist($step1);
        $dm->persist($step2);
        $dm->persist($step3);
        $dm->persist($step4);

        $dm->flush();

        $template = new JournalWorkflowTemplate();
        $template->setTitle('Basic Academic Workflow');
        $template->setFirstNode($step1);

        $dm->persist($template);
        $dm->flush();
        return $template;
    }

    /**
     * @TODO
     * @param type $dm
     */
    private function insertThirdTemplate($dm)
    {
        
    }

    public function getOrder()
    {
        return 23;
    }

}
