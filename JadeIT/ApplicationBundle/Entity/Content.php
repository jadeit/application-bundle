<?php

namespace JadeIT\ApplicationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JadeIT\ApplicationBundle\Service\Content as ContentService;

/**
 * Content
 */
class Content
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $format = 'html';

    /**
     * @var string
     */
    private $template;

    /**
     * Not persisted by the ORM.
     *
     * @var string
     */
    private $content;

    /**
     * @var boolean
     */
    private $active = true;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var \DateTime
     */
    private $added;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Content
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
     * Set title
     *
     * @param string $title
     * @return Content
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param String $content
     * @todo  Maybe we can pass the service in here to write to the file? Or add it
     * to a prePersist call? Check if prePersist can take custom arguments...
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent(ContentService $service = null)
    {
        if ($service === null) {
            return $this->content;
        } else {
            return $service->getContent($this);
        }
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * Set format
     *
     * @param String $format
     */
    public function setFormat($format) {
        $this->format = $format;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        if (empty($this->template)) {
            return ':Static:' . $this->getName() . '.' . $this->getFormat() . '.twig';
        } else {
            return $this->template;
        }
    }

    /**
     * Set template
     *
     * @param String $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Content
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set modified
     *
     * @param datetime $modified
     * @return Account
     */
    public function setModified($modified = null)
    {
        $modified = is_string($modified) || $modified === null ? new \DateTime($modified) : $modified;
        $this->modified = $modified;
        return $this;
    }

    /**
     * Get modified
     *
     * @return datetime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set added
     *
     * @param datetime $added
     * @return Account
     */
    public function setAdded($added = null)
    {
        $added = is_string($added) || $added === null ? new \DateTime($added) : $added;
        $this->added = $added;
        return $this;
    }

    /**
     * Get added
     *
     * @return datetime
     */
    public function getAdded()
    {
        return $this->added;
    }


    public function prePersist()
    {
        $this->setAdded();
        $this->setModified();
    }

    public function preUpdate()
    {
        $this->setModified();
    }
}
