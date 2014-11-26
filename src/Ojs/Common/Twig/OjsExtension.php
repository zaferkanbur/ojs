<?php

namespace Ojs\Common\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class OjsExtension extends \Twig_Extension
{

    private $container;

    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('issn', array($this, 'issnValidateFilter')),
        );
    }

    public function getFunctions()
    {
        return array(
            //'ojsuser' => new \Twig_Function_Method($this, 'checkUser', array('is_safe' => array('html'))),
            'hasRole' => new \Twig_Function_Method($this, 'hasRole'),
            'isSystemAdmin' => new \Twig_Function_Method($this, 'isSystemAdmin'),
            'isJournalManager' => new \Twig_Function_Method($this, 'isJournalManager'),
            'userjournals' => new \Twig_Function_Method($this, 'getUserJournals', array('is_safe' => array('html'))),
            'userclients' => new \Twig_Function_Method($this, 'getUserClients', array('is_safe' => array('html'))),
            'userJournalRoles' => new \Twig_Function_Method($this, 'getUserJournalRoles', array('is_safe' => array('html'))),
            'session' => new \Twig_Function_Method($this, 'getSession', array('is_safe' => array('html'))),
            'hasid' => new \Twig_Function_Method($this, 'hasId', array('is_safe' => array('html'))),
            'breadcrumb' => new \Twig_Function_Method($this, 'generateBreadcrumb', array('is_safe' => array('html'))),
            'currentJournal' => new \Twig_Function_Method($this, 'currentJournal', array('is_safe' => array('html')))
        );
    }

    /**
     *
     * @param array $list
     *                    $list =  array( array('link'=>'...','title'=>'...'), array('link'=>'...','title'=>'...') )
     */
    public function generateBreadcrumb($list = null)
    {

        $translator = $this->container->get('translator');
        $html = '<ol class="breadcrumb">';
        for ($i = 0; $i < count($list); ++$i) {
            $item = $list[$i];
            $html .=!isset($item['link']) ?
                    '<li class="active">' . $translator->trans($item['title']) . '</li>' :
                    '<li><a  href = "' . $item['link'] . '">' . $translator->trans($item['title']) . '</a></li>';
        }
        $html .= '</ol> ';

        return $html;
    }

    /**
     * Check osj user and return user data as array
     * @return \Ojs\UserBundle\Entity\User
     */
    public function checkUser()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            return $user;
        }

        return FALSE;
    }

    /**
     *
     * @param  mixed   $needle
     * @param  array   $haystack
     * @return boolean
     */
    public function hasId($needle, $haystack)
    {
        if (!is_array($haystack)) {
            return FALSE;
        }
        foreach ($haystack as $item) {
            if (isset($item['id']) && $item['id'] == $needle) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function getSession($session_key)
    {
        $session = new \Symfony\Component\HttpFoundation\Session\Session();

        return $session->get($session_key);
    }

    /**
     *
     * @return mixed
     */
    public function getUserJournals()
    {
        $session = new \Symfony\Component\HttpFoundation\Session\Session();

        return $session->get('userJournals');
    }

    /**
     *
     * @return mixed
     */
    public function getUserClients()
    {
        $session = new \Symfony\Component\HttpFoundation\Session\Session();

        return $session->get('userClients');
    }

    /**
     * get userJournalRoles session key
     * @return mixed
     */
    public function getUserJournalRoles()
    {
        $session = new \Symfony\Component\HttpFoundation\Session\Session();
        return $session->get('userJournalRoles');
    }

    /**
     * @return \Ojs\UserBundle\Entity\User
     */
    public function isSystemAdmin()
    {
        $user = $this->checkUser();
        if ($user) {
            foreach ($user->getRoles() as $role) {
                if ($role->getRole() == 'ROLE_SUPER_ADMIN') {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    public function hasRole($roleCode)
    {
        $userJournalRoles = $this->getSession('userJournalRoles');
        $user = $this->checkUser();
        if ($user && is_array($userJournalRoles)) {
            foreach ($userJournalRoles as $role) {
                if ($roleCode == $role->getRole()) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function isJournalManager()
    {
        return $this->hasRole('ROLE_JOURNAL_MANAGER');
    }

    /**
     * @todo reformat and validate given issn
     * @param  string $issn
     * @return string
     */
    public function issnValidateFilter($issn)
    {
        return $issn;
    }

    public function currentJournal()
    {
        /* @var $journalDomain \Ojs\Common\Model\JournalDomain */
        $journalDomain = $this->container->get('journal_domain');
        return $journalDomain->getCurrentJournal();
    }

    public function getName()
    {
        return 'ojs_extension';
    }

}
