<?php

namespace Ojs\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Ojs\AnalyticsBundle\Document\ObjectCommonStat;
use Ojs\AnalyticsBundle\Document\ObjectDownload;
use Ojs\AnalyticsBundle\Document\ObjectView;
use Ojs\AnalyticsBundle\Document\ObjectViews;
use Ojs\AnalyticsBundle\Updater;
use Ojs\AnalyticsBundle\Updater\UpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 * @package Ojs\AnalyticsBundle\Command
 */
class UpdateCommand extends ContainerAwareCommand
{

    /** @var  DocumentManager $dm */
    private $dm;

    /** @var  Serializer $serializer */
    private $serializer;

    /** @var array $objects */
    private $objects = [
        'article' => 'OjsJournalBundle:Article',
        'journal' => 'OjsJournalBundle:Journal',
        'institution' => 'OjsJournalBundle:Institution',
        'file' => 'OjsJournalBundle:File',
    ];

    /** @var  EntityManager $em */
    private $em;

    /** @var  OutputInterface $output */
    public $output;

    protected function configure()
    {
        $this->setName("ojs:analytics:update")
            ->setDescription("Analytics total data updater")
            ->addArgument("type", InputArgument::REQUIRED, "What is the type you want to update? [view, download]")
            ->addOption('test', 't', null, "Test mode");
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $this->serializer = $this->getContainer()->get('jms_serializer');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $type = $input->getArgument('type');
        $this->output = $output;

        switch ($type) {
            case "view":
                $this->updateViewData($output);
                break;
            case "download":
                $this->updateDownloadData($output);
                break;
            case "common":
                $this->updateCommonData();
                break;
            default:
                $output->writeln("The value may have only 'view', 'download', 'common'");
                break;
        }
    }

    private function getObject($object)
    {
        /** @var ObjectViews $object */
        $entity = $this->objects[$object->getEntity()];
        $object = $this->em->find($entity, $object->getObjectId());

        return $object;
    }

    private function updateViewData(OutputInterface $output)
    {
        try {
            $progress = $this->getHelper('progress');

            $allViews = $this->dm->getRepository('OjsAnalyticsBundle:ObjectViews')->findAll();
            $counts = [];
            foreach ($allViews as $view) {
                /** @var ObjectViews $view */
                if (isset($counts[$view->getPageUrl()])) {
                    $counts[$view->getPageUrl()]->total++;
                } else {
                    $counts[$view->getPageUrl()] = new \stdClass();
                    $counts[$view->getPageUrl()]->total = 1;
                    $counts[$view->getPageUrl()]->id = $view->getObjectId();
                    $counts[$view->getPageUrl()]->entity = $view->getEntity();
                    $counts[$view->getPageUrl()]->rawData = $this->serializer->serialize(
                        $this->getObject($view),
                        'json'
                    );
                }
            }
            $progress->start($output, count($counts));
            foreach ($counts as $key => $object) {
                echo $key.'=>'.$object->total."\n";
                //check
                $totalView = $this->dm->getRepository("OjsAnalyticsBundle:ObjectView")
                    ->findOneBy(['pageUrl' => $key, 'objectId' => $object->id, 'entity' => $object->entity]);

                $totalView = $totalView ? $totalView : new ObjectView();

                $totalView->setPageUrl($key);
                $totalView->setTotal($object->total);
                $totalView->setObjectId($object->id);
                $totalView->setEntity($object->entity);
                $totalView->setRawData($object->rawData);
                $this->dm->persist($totalView);

                $progress->advance();
            }
            $this->dm->flush();

            $progress->finish();
            $output->writeln("Successfully");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln("An error has occured");
        }
    }

    private function updateDownloadData(OutputInterface $output)
    {
        try {
            $progress = $this->getHelper('progress');

            $allDownloads = $this->dm->getRepository("OjsAnalyticsBundle:ObjectDownloads")->findAll();
            $counts = [];
            foreach ($allDownloads as $download) {
                $realObject = $this->getObject($download);

                if (isset($count[$download->getFilePath()])) {
                    $counts[$download->getFilePath()]->total++;
                } else {
                    $counts[$download->getFilePath()] = new \stdClass();
                    $counts[$download->getFilePath()]->total = 1;
                    $counts[$download->getFilePath()]->id = $download->getObjectId();
                    $counts[$download->getFilePath()]->entity = $download->getEntity();
                    $counts[$download->getFilePath()]->rawData = $this->serializer->serialize($realObject, 'json');
                }
            }
            $progress->start($output, count($counts));

            foreach ($counts as $key => $object) {
                //check data
                $totalDownload = $this->dm
                    ->getRepository("OjsAnalyticsBundle:ObjectDownload")
                    ->findOneBy(['filePath' => $key, 'objectId' => $object->id, 'entity' => $object->entity]);
                $totalDownload = $totalDownload ? $totalDownload : new ObjectDownload();

                $totalDownload->setTotal($object->total);
                $totalDownload->setFilePath($key);
                $totalDownload->setRawData($object->rawData);
                $totalDownload->setEntity($object->entity);
                $totalDownload->setObjectId($object->id);

                $this->dm->persist($totalDownload);
                $progress->advance();
            }
            $this->dm->flush();

            $progress->finish();
            $output->writeln("Successfully");
        } catch (\Exception $e) {
            $output->writeln("An error has occured on file ".$e->getFile()." on line ".$e->getLine());
            $output->writeln($e->getMessage());
        }
    }

    private function updateCommonData()
    {
        $updates = [
            'JournalViewCount',
            //   'DailyReviewCount',
            //   'AcceptedArticleCount',
            //   'DeclinedArticleCount',
            //   'RevisedArticleCount',
            'UserCount',
            'ReaderCount',
            'MemberCount',
            'PublishedIssueCount',
        ];

        foreach ($updates as $update) {
            $updater = 'Ojs\\AnalyticsBundle\\Updater\\'.$update.'Updater';
            /** @var UpdaterInterface $statObject */
            $statObject = new $updater(
                $this->em,
                $this->dm,
                $this->getContainer()->get('okulbilisimcmsbundle.twig.post_extension')
            );
            foreach ($statObject->count() as $objectId => $stat) {
                $object = new ObjectCommonStat();
                $object->setDate(new \DateTime())
                    ->setCount(count($stat))
                    ->setEntity($statObject->getObject($objectId))
                    ->setObject($objectId)
                    ->setStatType(constant("Ojs\\AnalyticsBundle\\Document\\ObjectCommonStat::".$update));
                $this->dm->persist($object);
            }
            $this->output->writeln("<info>{$update} operation completed.</info>");
        }
        $this->dm->flush();

    }
}
