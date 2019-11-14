<?php

namespace Marello\Bundle\CatalogBundle\Controller;

use Marello\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route(path="/", name="marello_category_index")
     * @AclAncestor("marello_category_view")
     * @Template
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloCatalogBundle:Category'];
    }

    /**
     * @Route(path="/create", name="marello_category_create")
     * @AclAncestor("marello_category_create")
     * @Template("MarelloCatalogBundle:Category:update.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        return $this->update(new Category());
    }

    /**
     * @Route(path="/update/{id}", requirements={"id"="\d+"}, name="marello_category_update")
     * @AclAncestor("marello_category_update")
     * @Template("MarelloCatalogBundle:Category:update.html.twig")
     *
     * @param Category $category
     *
     * @return array
     */
    public function updateAction(Category $category)
    {
        return $this->update($category);
    }

    /**
     * @param Category $category
     *
     * @return array
     */
    protected function update(Category $category)
    {
        $handler = $this->get('marello_catalog.category.form.handler');

        if ($handler->process($category)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.catalog.ui.category.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($category);
        }

        return [
            'entity' => $category,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Route(path="/view/{id}", requirements={"id"="\d+"}, name="marello_category_view")
     * @AclAncestor("marello_category_view")
     * @Template("MarelloCatalogBundle:Category:view.html.twig")
     *
     * @param Category $category
     *
     * @return array
     */
    public function viewAction(Category $category)
    {
        return [
            'entity' => $category,
        ];
    }
}
