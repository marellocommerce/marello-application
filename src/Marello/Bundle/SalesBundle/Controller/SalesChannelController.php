<?php

namespace Marello\Bundle\SalesBundle\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Config\Route("/channel")
 */
class SalesChannelController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Method("GET")
     * @Config\Template
     * @Security\Acl(
     *      id="marello_sales_channel_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="MarelloSalesBundle:SalesChannel"
     * )
     */
    public function indexAction()
    {
        return [
            'entity_class' => 'Marello\Bundle\SalesBundle\Entity\SalesChannel',
        ];
    }

    /**
     * @Config\Route("/delete/{id}", requirements={"id":"\d+"})
     * @Config\Method("DELETE")
     * @Security\Acl(
     *      id="marello_sales_channel_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="MarelloSalesBundle:SalesChannel"
     * )
     *
     * @param SalesChannel $channel
     *
     * @return RedirectResponse
     */
    public function deleteAction(SalesChannel $channel)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($channel);

        try {
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            /*
             * In case a foreign constraint would be violated when sales channel is removed,
             * keep it and display message.
             *
             * Foreign constraint violation in this case means that there are still entities in marello,
             * which are associated to this particular channel. These should be deleted before channel itself.
             *
             * TODO: Display this message. When delete action returns code 500, it is overridden in js with a different one.
             *       Code 500 is the correct one that should be returned, so probably a modification in js will be needed.
             */
            $this->addFlash('error', 'marello.sales.messages.sales_channel_has_associations');

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloSalesBundle:SalesChannel:update.html.twig")
     * @Security\Acl(
     *      id="marello_sales_channel_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="MarelloSalesBundle:SalesChannel"
     * )
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new SalesChannel(), $request);
    }

    /**
     * @Config\Route("/update/{id}", requirements={"id":"\d+"})
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @Security\Acl(
     *      id="marello_sales_channel_update",
     *      type="entity",
     *      permission="UPDATE",
     *      class="MarelloSalesBundle:SalesChannel"
     * )
     *
     * @param SalesChannel $channel
     * @param Request      $request
     *
     * @return array
     */
    public function updateAction(SalesChannel $channel, Request $request)
    {
        return $this->update($channel, $request);
    }

    /**
     * Handles common update and create functionality.
     *
     * @param SalesChannel $channel
     * @param Request      $request
     *
     * @return array|RedirectResponse
     */
    private function update(SalesChannel $channel, Request $request)
    {
        $form = $this->createForm('marello_sales_channel', $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($channel);
            $entityManager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_sales_saleschannel_edit',
                    'parameters' => ['id' => $channel->getId()],
                ],
                ['route' => 'marello_sales_saleschannel_index'],
                $channel
            );
        }

        return [
            'entity' => $channel,
            'form'   => $form->createView(),
        ];
    }
}
