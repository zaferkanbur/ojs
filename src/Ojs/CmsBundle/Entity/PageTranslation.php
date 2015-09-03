<?php

namespace Ojs\CmsBundle\Entity;

use Ojs\CoreBundle\Entity\DisplayTrait;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

class PageTranslation extends AbstractTranslation
{
    use DisplayTrait;
    /**
     * @Prezent\Translatable(targetEntity="Ojs\CmsBundle\Entity\Page")
     */
    protected $translatable;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}
