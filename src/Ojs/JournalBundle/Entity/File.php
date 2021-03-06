<?php

namespace Ojs\JournalBundle\Entity;

use APY\DataGridBundle\Grid\Mapping as GRID;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Translatable\Translatable;
use Ojs\Common\Entity\GenericEntityTrait;

/**
 * File
 * @GRID\Source(columns="id,name,mimeType,size")
 */
class File implements Translatable
{
    use GenericEntityTrait;
    /**
     * @var integer
     * @GRID\Column(title="id")
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     * @GRID\Column(title="name")
     */
    private $name;

    /**
     * @var string
     * @GRID\Column(title="file.type")
     */
    private $mimeType;

    /**
     * @var string
     * @GRID\Column(title="size")
     */
    private $size;

    /**
     * @var Collection
     */
    private $articleFiles;

    protected $translations;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->articleFiles = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(FileTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    public function setTranslations($translations)
    {
        foreach($translations as $translation){
            $this->addTranslation($translation);
        }
    }

    /**
     * Set path
     *
     * @param  string $path
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mimeType
     *
     * @param  string $mimeType
     * @return File
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set size
     *
     * @param  string $size
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get articleFiles
     *
     * @return ArrayCollection
     */
    public function getArticleFiles()
    {
        return $this->articleFiles;
    }

    /**
     * Set articleFiles
     *
     * @param  \Doctrine\Common\Collections\ArrayCollection $articleFiles
     * @return File
     */
    public function setArticleFiles(ArrayCollection $articleFiles = null)
    {
        $this->articleFiles = $articleFiles;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $issueFiles;


    /**
     * Add articleFiles
     *
     * @param \Ojs\JournalBundle\Entity\ArticleFile $articleFiles
     * @return File
     */
    public function addArticleFile(\Ojs\JournalBundle\Entity\ArticleFile $articleFiles)
    {
        $this->articleFiles[] = $articleFiles;

        return $this;
    }

    /**
     * Remove articleFiles
     *
     * @param ArticleFile $articleFiles
     */
    public function removeArticleFile(ArticleFile $articleFiles)
    {
        $this->articleFiles->removeElement($articleFiles);
    }

    /**
     * Add issueFiles
     *
     * @param IssueFile $issueFiles
     * @return File
     */
    public function addIssueFile(IssueFile $issueFiles)
    {
        $this->issueFiles[] = $issueFiles;

        return $this;
    }

    /**
     * Remove issueFiles
     *
     * @param IssueFile $issueFiles
     */
    public function removeIssueFile(IssueFile $issueFiles)
    {
        $this->issueFiles->removeElement($issueFiles);
    }

    /**
     * Get issueFiles
     *
     * @return Collection
     */
    public function getIssueFiles()
    {
        return $this->issueFiles;
    }
}
