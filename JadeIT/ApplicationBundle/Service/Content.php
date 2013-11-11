<?php

namespace JadeIT\ApplicationBundle\Service;

use \Twig_Environment;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use JadeIT\ApplicationBundle\Entity\Content as ContentEntity;
use JadeIT\ApplicationBundle\Event\ContentEvent;

class Content implements EventSubscriberInterface
{
    private $twig;

    private $fileSystem;

    private $location;

    public function __construct(Twig_Environment $twig, $location, Filesystem $fileSystem)
    {
        $this->twig = $twig;
        $this->fileSystem = $fileSystem;
        $this->location = $location;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'jade.i.t.content.create' => 'onNewContent',
            'jade.i.t.content.update' => 'onUpdateContent',
        );
    }

    public function onNewContent(ContentEvent $event)
    {
        $this->putContent($event->getContent());
    }

    public function onUpdateContent(ContentEvent $event)
    {
        $this->putContent($event->getContent());
    }

    public function putContent(ContentEntity $entity)
    {
        // This can potentially not work
        $folder = realpath($this->location);
        if (is_writable($folder) === false) {
            throw new \RuntimeException('Cannot find static templates location: ' . $this->location);
        }
        $filename = $folder . '/' . $entity->getName() . '.html.twig';
        $template = $this->twig->render(
            'JadeITApplicationBundle:Content:static.html.twig.twig',
            array('content' => trim($entity->getContent()))
        );

        // All code except the return can be replace by this one line once it's live
        // in Symfony
        //$this->fileSystem->dumpFile($filename, $template. 0644);
        if (is_writable(dirname($filename)) === false) {
            throw new \RuntimeException('Cannot write to static templates location: ' . dirname($filename));
        }
        $result = file_put_contents($filename, $template);
        return $template;
    }

    public function getContent(ContentEntity $entity, $html = false)
    {
        $template = $this->twig->loadTemplate($entity->getTemplate());
        return $template->renderBlock($html ? 'content' : 'markdown', array());
    }
}
