<?php

namespace Ojs\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

class PageTranslation extends AbstractPersonalTranslation
{
    protected $object;
}