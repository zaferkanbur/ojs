<?php

namespace Ojs\JournalBundle\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Entity\Journal;
use Ojs\SiteBundle\Acl\JournalRoleSecurityIdentity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class AclOrmListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Journal) {
            $journal = $entity;
            $aclManager = $this->container->get('problematic.acl_manager');
            $viewEdit = (new MaskBuilder())
                ->add('view')
                ->add('edit')->get();
            $viewEditDelete = (new MaskBuilder())
                ->add('view')
                ->add('edit')
                ->add('delete')->get();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_JOURNAL_MANAGER'))
                ->permit(MaskBuilder::MASK_OWNER)->save();
            $aclManager->on($journal)->field('adminMenu')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_JOURNAL_MANAGER')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_JOURNAL_MANAGER')
            )
                ->permit(MaskBuilder::MASK_OWNER)->save();
            $aclManager->on($journal)->field('sections')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_JOURNAL_MANAGER')
            )
                ->permit(MaskBuilder::MASK_OWNER)->save();
            $aclManager->on($journal)->field('articles')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_JOURNAL_MANAGER')
            )
                ->permit($viewEditDelete)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('adminMenu')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('sections')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit(MaskBuilder::MASK_OWNER)->save();
            $aclManager->on($journal)->field('issues')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit(MaskBuilder::MASK_OWNER)->save();
            $aclManager->on($journal)->field('articles')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit($viewEditDelete)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_AUTHOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('articles')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_AUTHOR'))
                ->permit(MaskBuilder::MASK_CREATE)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_PROOFREADER'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('adminMenu')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_PROOFREADER')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_PROOFREADER')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('sections')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_PROOFREADER')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('issues')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_PROOFREADER')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_COPYEDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('adminMenu')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_COPYEDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_COPYEDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('sections')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_COPYEDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('issues')->to(new JournalRoleSecurityIdentity($journal, 'ROLE_COPYEDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_LAYOUT_EDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('adminMenu')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_LAYOUT_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_LAYOUT_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('sections')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_LAYOUT_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('issues')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_LAYOUT_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_SECTION_EDITOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('adminMenu')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SECTION_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SECTION_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('sections')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SECTION_EDITOR')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('issues')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SECTION_EDITOR')
            )
                ->permit($viewEdit)->save();

            $aclManager->on($journal)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_SUBSCRIPTION_MANAGER'))
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('adminMenu')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SUBSCRIPTION_MANAGER')
            )
                ->permit(MaskBuilder::MASK_VIEW)->save();
            $aclManager->on($journal)->field('boards')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SUBSCRIPTION_MANAGER')
            )
                ->permit($viewEdit)->save();
            $aclManager->on($journal)->field('sections')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SUBSCRIPTION_MANAGER')
            )
                ->permit($viewEdit)->save();
            $aclManager->on($journal)->field('issues')->to(
                new JournalRoleSecurityIdentity($journal, 'ROLE_SUBSCRIPTION_MANAGER')
            )
                ->permit($viewEdit)->save();
        } elseif ($entity instanceof Article) {
            $journal = $entity->getJournal();
            $aclManager = $this->container->get('problematic.acl_manager');

            $viewEdit = (new MaskBuilder())
                ->add('view')
                ->add('edit')->get();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_JOURNAL_MANAGER'))
                ->permit(MaskBuilder::MASK_OWNER)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_EDITOR'))
                ->permit(MaskBuilder::MASK_OWNER)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_AUTHOR'))
                ->permit(MaskBuilder::MASK_VIEW)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_PROOFREADER'))
                ->permit($viewEdit)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_COPYEDITOR'))
                ->permit($viewEdit)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_LAYOUT_EDITOR'))
                ->permit($viewEdit)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_SECTION_EDITOR'))
                ->permit($viewEdit)->save();

            $aclManager->on($entity)->to(new JournalRoleSecurityIdentity($journal, 'ROLE_SUBSCRIPTION_MANAGER'))
                ->permit($viewEdit)->save();
        }
    }
}
