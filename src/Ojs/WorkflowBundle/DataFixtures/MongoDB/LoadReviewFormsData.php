<?php

namespace Ojs\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ojs\WorkflowBundle\Document\ReviewForm;

/**
 * @todo
 * Create sample workflow
 */
class LoadReviewFormsData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $dm)
    {
        $form1 = new ReviewForm();
        $form1->setJournalId(1);
        $form1->setTitle('Article Evaluation Form');

        $form2 = new ReviewForm();
        $form2->setJournalId(1);
        $form2->setTitle('Case Report Review Form');

        $dm->persist($form1);
        $dm->persist($form2);
        $dm->flush();

        $this->setReference('form1', $form1);
        $this->setReference('form2', $form2);
    }

    public function getOrder()
    {
        return 20;
    }
}
