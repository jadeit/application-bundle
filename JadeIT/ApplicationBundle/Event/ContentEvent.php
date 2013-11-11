<?php

namespace JadeIT\ApplicationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

use JadeIT\ApplicationBundle\Entity\Content;

class ContentEvent extends Event
{
    private $content;
    private $request;

    public function __construct(Content $content, Request $request)
    {
        $this->content = $content;
        $this->request = $request;
    }

    /**
     * Return the Content associated with the event.
     *
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the Content associated with the event.
     *
     * @param Content $content
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }


    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Set request
     *
     * @param Request $request
     */
    public function setRequest(Request $request) {
        $this->request = $request;

        return $this;
    }
}
