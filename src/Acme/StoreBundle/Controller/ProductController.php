<?php

namespace Acme\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Acme\StoreBundle\Entity\Product;
use Acme\StoreBundle\Entity\Category;
use Acme\StoreBundle\Form\ProductType;


class ProductController extends Controller
{
	/* READ PRODUCT */
	public function showAllAction()
    {
        $products = $this->getDoctrine()
                         ->getRepository('AcmeStoreBundle:Product')
                         ->findAllOrderedByName();

        return $this->render('AcmeStoreBundle:Product:showAll.html.twig', array('products' => $products));
    }

	/* CREATE PRODUCT */
	/* Build Add Product Form */
    public function createProductAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product, array('method' => 'POST',));        
        $form->handleRequest($request);

        if ($form->isValid()) {
            if( $this->createProductExecAction($request) ) {
                $this->get('session')->getFlashBag()->add('notice','Prodotto inserito con successo');
                return $this->redirect($this->generateUrl('acme_store_product_create'));
            }
        }
        return $this->render('AcmeStoreBundle:Product:manageProduct.html.twig', array('form' => $form->createView(),));
    }

    /* Exec Create new Product Statements */
    public function createProductExecAction(Request $request)
    {
        $formVars = $request->request->get('product');

        $em = $this->getDoctrine()->getManager();

        /* First step Create the Product */
        $product = new Product();
        $product->setName($formVars['name']);
        $product->setPrice($formVars['price']);
        $product->setDescription($formVars['description']);

        $errors = $this->get('validator')->validate($product);
        
        if ( count($errors) > 0 ) {
            return false;
        } else {
            try {
                $em->persist($product);
                $em->flush();
            } catch (\Exception $e) {
                $em->resetManager();
                return false;
            }

            /* Second step Update the Inserted Product with the Category ID (Relational DB) */
            $category = $em->getRepository("AcmeStoreBundle:Category")->find($formVars['category']);
            $newproduct = $em->getRepository("AcmeStoreBundle:Product")->find($product->getId());
            $newproduct->setCategory($category);

            try {
                $em->persist($newproduct);
                $em->flush();
            } catch (\Exception $e) {
                $this->getDoctrine()->resetManager();
                return false;
            }
            /** Update the id_category field of the new product with parameter $categoryId */
            return true;
        }
    }

    # UPDATE PRODUCT
    /* Build Update Product Form */  
    public function updateProductAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $productItem = $em->getRepository('AcmeStoreBundle:Product')->find($id);

        $product = new Product();
        $product->setName( $productItem->getName() );
        $product->setDescription( $productItem->getDescription() );
        $product->setPrice( $productItem->getPrice() );
        $product->setCategory( $productItem->getCategory() );

        $form = $this->createForm(ProductType::class, $product, array('method' => 'POST'));
        $form->handleRequest($request);

        if ($form->isValid())
            $this->updateProductExecAction($request, $id);

        return $this->render('AcmeStoreBundle:Product:manageProduct.html.twig', array('form' => $form->createView(),));   
    }

    /* Exec Update new Product Statements */
    public function updateProductExecAction(Request $request, $id)
    {
        $formVars = $request->request->get('product');

        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository("AcmeStoreBundle:Category")->find($formVars['category']);
        $product  = $em->getRepository('AcmeStoreBundle:Product')->find($id);
        
        $product->setName($formVars['name']);
        $product->setPrice($formVars['price']);
        $product->setDescription($formVars['description']);
        $product->setCategory($category);

        $errors = $this->get('validator')->validate($product);

        if ( count($errors) > 0 ) {
            $this->get('session')->getFlashBag()->add('noticeError','Nessuna categoria con questo Id');
        } else {
            $exception = false;
            try {
                $em->persist($product);
                $em->flush();
            } catch (\Exception $e) {
                $exception = true;
                $this->getDoctrine()->resetManager();
                $this->get('session')->getFlashBag()->add('noticeError','Errore in fase di aggiornamento');
            }
            if (!$exception) 
                $this->get('session')->getFlashBag()->add('notice','Prodotto aggiornato con successo');
        }

        return $this->redirect($this->generateUrl('acme_store_product_update', array('id' => $id ) ) );      
    }

    /* DELETE PRODUCT */
    /* Exec Delete Product Statements */
    public function deleteProductAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AcmeStoreBundle:Product')->find($id);

        if(!$product)
            $this->get('session')->getFlashBag()->add('noticeError', 'Nessun prodotto con questo Id trovato');

        $em->remove($product);
        $em->flush();

        $this->get('session')->getFlashBag()->add('notice','Prodotto eliminato con successo');
    
        return $this->redirect($this->generateUrl('acme_store_product_show_all'));
    }
}
