<?php

namespace JadeIT\ApplicationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use JadeIT\ApplicationBundle\Event\ContentEvent;
use JadeIT\ApplicationBundle\Entity\Content;
use JadeIT\ApplicationBundle\Form\ContentType;

/**
 * Content controller.
 *
 */
class ContentController extends Controller
{
    /**
     * Lists all Content entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('JadeITApplicationBundle:Content')->findAll();

        return $this->render(
            'JadeITApplicationBundle:Content:index.html.twig',
            array(
                'entities' => $entities,
            )
        );
    }

    /**
     * Creates a new Content entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Content();
        $form = $this->createCreateForm($entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setContent($request->request->get('content'));

            $em->persist($entity);

            $event = new ContentEvent($entity, $request);
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch('jade.i.t.content.create', $event);

            $em->flush();

            return $this->redirect($this->generateUrl('content_show', array('id' => $entity->getName())));
        }

        return $this->render(
            'JadeITApplicationBundle:Content:new.html.twig',
            array(
                'entity' => $entity,
                'form'   => $form->createView(),
            )
        );
    }

    /**
    * Creates a form to create a Content entity.
    *
    * @param Content $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Content $entity)
    {
        $form = $this->createForm(new ContentType(), $entity, array(
            'action' => $this->generateUrl('content_create'),
            'method' => 'POST',
            'attr' => array('class' => 'form-horizontal'),
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new Content entity.
     *
     */
    public function newAction()
    {
        $entity = new Content();
        $form   = $this->createCreateForm($entity);

        return $this->render(
            'JadeITApplicationBundle:Content:new.html.twig',
            array(
                'entity' => $entity,
                'form'   => $form->createView(),
            )
        );
    }

    /**
     * Finds and displays a Content entity.
     *
     */
    public function showAction(Request $request, $id, $maxAge = null, $sharedAge = null, $private = null, $file = false)
    {
        // Read
        if ($file === false) {
            $em = $this->getDoctrine()->getManager();

            $entity = $em->getRepository('JadeITApplicationBundle:Content')->findOneByName($id);
        }

        // Check for a static file
        if (empty($entity)) {
            $entity = new Content();
            $entity->setName($id);
            $entity->setFormat($request->getRequestFormat());
        }

        if (empty($entity)) {
            throw $this->createNotFoundException('Unable to find Content entity.');
        }

        // Fire the Read Content Event
        $event = new ContentEvent($entity, $request);
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch('notes.events.content.read', $event);

        // Render
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $deleteForm = $this->createDeleteForm($entity->getId())->createView();
        } else {
            $deleteForm = false;
        }

        try {
            $response = $this->container->get('templating')->renderResponse(
                $entity->getTemplate(),
                array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm,
                )
            );
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('Could not find ' . $entity->getName(), $e);
        }

        if ($maxAge) {
            $response->setMaxAge($maxAge);
        }
        if ($sharedAge) {
            $response->setSharedMaxAge($sharedAge);
        }
        if ($private) {
            $response->setPrivate();
        } elseif ($private === false || (null === $private && ($maxAge || $sharedAge))) {
            $response->setPublic($private);
        }

        return $response;
    }

    /**
     * Displays a form to edit an existing Content entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('JadeITApplicationBundle:Content')->findOneByName($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Content entity.');
        }

        $content = $entity->getContent($this->container->get('notes_application.content'));
        $editForm = $this->createForm(new ContentType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render(
            'JadeITApplicationBundle:Content:edit.html.twig',
            array(
                'entity'      => $entity,
                'content'     => $content,
                'edit_form'   => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Edits an existing Content entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NotesApplicationBundle:Content')->findOneByName($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Content entity.');
        }

        $deleteForm = $this->createDeleteForm($entity->getId());
        $editForm = $this->createForm(new ContentType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);

            $entity->setContent($request->request->get('content'));

            // Fire the Updated Content Event
            $event = new ContentEvent($entity);
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch('jade.i.t.content.update', $event);

            // Delay the flush so that the event can deal with the updated entity first
            $em->flush();

            return $this->redirect($this->generateUrl('content_show', array('id' => $id)));
        }

        return $this->render(
            'NotesApplicationBundle:Content:edit.html.twig',
            array(
                'entity'      => $entity,
                'edit_form'   => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
            )
        );
    }

    /**
     * Deletes a Content entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('JadeITApplicationBundle:Content')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Content entity.');
            }

            $em->remove($entity);

            // Fire the Deleted Content Event
            $event = new ContentEvent($entity);
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch('notes.events.content.delete', $event);

            // Delay the flush so that the event can deal with the deleted entity first
            $em->flush();
        }

        return $this->redirect($this->generateUrl('content'));
    }

    /**
     * Creates a form to delete a Content entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
