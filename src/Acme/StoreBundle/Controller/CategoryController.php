<?php

namespace Acme\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Acme\StoreBundle\Entity\Product;
use Acme\StoreBundle\Entity\Category;
use Acme\StoreBundle\Form\CategoryType;

class CategoryController extends Controller
{
	/* READ */

	public function showAllAction()
	{
        $categories = $this->getDoctrine()
                 ->getRepository('AcmeStoreBundle:Category')
                 ->findAllOrderedByName();

        return $this->render('AcmeStoreBundle:Category:showAll.html.twig', array('rows' => $categories));
	}

	/* CREATE */

	/* Build Add Category Form */
	public function createCategoryAction(Request $request)
	{
		$category = new Category();

        $form = $this->createForm( CategoryType::class, $category, array('method' => 'POST',) );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if( $this->createCategoryExecAction($request) ) {   
                $this->get('session')->getFlashBag()->add('notice', 'Categoria inserita con successo');
            } else {
            	$this->get('session')->getFlashBag()->add('noticeError', 'Errore in fase di inserimento');            
            }
            return $this->redirect($this->generateUrl('acme_store_category_create'));
        }
        return $this->render('AcmeStoreBundle:Category:manageCategory.html.twig', array('form' => $form->createView(),));
	}

	/* Exec Create new Product Statements */
    public function createCategoryExecAction(Request $request)
    {
        $category = new Category();
        $formVars = $request->request->get('category');
        $category->setName( $formVars['name'] );

        $validator = $this->get('validator');
        $errors = $validator->validate($category);

        if ( count($errors) > 0 ) {
            return false;
        } else {
            try {
                $em = $this->getDoctrine()->getManager();            
                $em->persist($category);
                $em->flush();
            } catch (\Exception $e) {
                $this->getDoctrine()->resetManager();
                return false;
            }
            return true;
        }
    }



	/* UPDATE */

	/* Build Update Category Form */  
    public function updateCategoryAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $categoryItem = $em->getRepository('AcmeStoreBundle:Category')->find($id);

        $category = new Category();
        $category->setName( $categoryItem->getName() );
        
        $form = $this->createForm(CategoryType::class, $category, array('method' => 'POST'));
        $form->handleRequest($request);

        if ($form->isValid())
            $this->updateCategoryExecAction($request, $id);

        return $this->render('AcmeStoreBundle:Category:manageCategory.html.twig', array('form' => $form->createView(),));   
    }

    /* Exec Update new Product Statements */
    public function updateCategoryExecAction(Request $request, $id)
    {
        $formVars = $request->request->get('category');

        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository("AcmeStoreBundle:Category")->find($id);
        $category->setName($formVars['name']);
        
        $errors = $this->get('validator')->validate($category);

        if ( count($errors) > 0 ) {
            $this->get('session')->getFlashBag()->add('noticeError','Nessuna categoria con questo Id');
        } else {
            $exception = false;
            try {
                $em->persist($category);
                $em->flush();
            } catch (\Exception $e) {
                $exception = true;
                $this->getDoctrine()->resetManager();
                $this->get('session')->getFlashBag()->add('noticeError','Errore in fase di aggiornamento');
            }
            if (!$exception)         
                $this->get('session')->getFlashBag()->add('notice','Categoria aggiornata con successo');
        }
        
        return $this->redirect($this->generateUrl('acme_store_category_update', array('id' => $id ) ) );
    }


	/* DELETE */
    
    /* Exec Delete Category Statements */
    public function deleteCategoryAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('AcmeStoreBundle:Category')->find($id);

        if (!$category)
            $this->get('session')->getFlashBag()->add('noticeError', 'Nessuna categoria con questo Id trovata');
        try{
            $em->remove($category);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice','Categoria eliminata con successo');
        } catch (\Exception $e) {
            $this->getDoctrine()->resetManager();
            $this->get('session')->getFlashBag()->add('noticeError', "Errore! Non puoi eliminare una categoria che ha dei Prodotti associati");
        }    
        return $this->redirect($this->generateUrl('acme_store_category_show_all'));
    }
}

