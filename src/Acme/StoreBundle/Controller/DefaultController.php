<?php

namespace Acme\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Acme\StoreBundle\Entity\Product;
use Acme\StoreBundle\Entity\Category;
use Acme\StoreBundle\Form\ProductType;


class DefaultController extends Controller
{
    private $data;

    public function indexAction()
    {
        return $this->render('AcmeStoreBundle:Default:index.html.twig');
    }

    /*
        ################# CREATE NEW PRODUCT METHODS #################  
    */

    /* Execute the SQL Statements */

    public function createCategoryAction($name)
    {
        $category = new Category();

        $category->setName(ucfirst($name));

        $em = $this->getDoctrine()->getManager();

        if (!$em->getRepository('AcmeStoreBundle:Category')->findOneByName($name)) {
            $em->persist($category);
            $em->flush();
            return new Response( 'Creata categoria con id ' . $category->getId() );
        } else {
            return new Response( 'Categoria ' . $name . ' gia presente nel DB' );
        }
    }

    /* Build Add Product Form */    

    public function productAddAction(Request $request)
    {
        $product = new Product();

        $form = $this->createForm(new ProductType(), $product, array(
                'method' => 'POST',
            ));
        
        $form->handleRequest($request);

        if ($form->isValid()) {

            if( $this->createProductAction($request) ) {
                $this->get('session')->getFlashBag()->add(
                            'notice',
                            'Prodotto inserito con successo'
                );
                return $this->redirect($this->generateUrl('acme_store_manage_product'));
            }
        }

        return $this->render('AcmeStoreBundle:Product:manageProduct.html.twig', array('form' => $form->createView(),));
    }
    
    /* Return Bool value and execute the SQL Statements */    

    public function createProductAction(Request $request)
    {
        
        $formVars = $request->request->get('product');

        $product = new Product();
        
        $product->setName($formVars['name']);
        $product->setPrice($formVars['price']);
        $product->setDescription($formVars['description']);
        
        $categoryId = $formVars['category'];

        $em = $this->getDoctrine()->getManager();

        $validator = $this->get('validator');
        $errors = $validator->validate($product);

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
            
            $newproduct = $em->getRepository("AcmeStoreBundle:Product")->find($product->getId());
            /** Create new product and retrive its informations in $newproduct */

            $repository = $em->getRepository("AcmeStoreBundle:Category")->find($categoryId);

            $newproduct->setCategory($repository);

            try {

                $em->persist($newproduct);
                $em->flush();
            
            } catch (\Exception $e) {
                 
                $em->resetManager();
                return false;
            } 
            /** Update the id_category field of the new product with parameter $categoryId */

            return true;
        }
    }

    /*
        #################  UPDATE PRODUCT METHODS #################
    */


    /* Build Update Product Form */    

    public function productUpdateAction(Request $request, $id)
    {
        $product = new Product();

        $em = $this->getDoctrine()->getManager();

        $productItem = $em->getRepository('AcmeStoreBundle:Product')->find($id);

        $product->setName( $productItem->getName() );
        $product->setDescription( $productItem->getDescription() );
        $product->setPrice( $productItem->getPrice() );
        $product->setCategory( $productItem->getCategory() );

        $form = $this->createForm(new ProductType(), $product, array('method' => 'POST'));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->updateProductAction($request, $id);
        }

        return $this->render('AcmeStoreBundle:Product:manageProduct.html.twig', array('form' => $form->createView()));   
    }

    /* Return Bool value and execute the SQL Statements */    

    public function updateProductAction(Request $request, $id)
    {
        $formVars = $request->request->get('product');

        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository('AcmeStoreBundle:Product')->find($id);
        $category = $em->getRepository("AcmeStoreBundle:Category")->find($formVars['category']);

        //$product = new Product();
        $product->setName($formVars['name']);
        $product->setPrice($formVars['price']);
        $product->setDescription($formVars['description']);
        $product->setCategory($category);

        $errors = $this->get('validator')->validate($product);

        if ( count($errors) > 0 ) {

            $this->get('session')->getFlashBag()->add('notice', 'Nessuna categoria con questo Id');

        } else {
            try {
                $em->persist($product);
                $em->flush();          
            } catch (\Exception $e) {
                $em->resetManager();
                $this->get('session')->getFlashBag()->add('notice', 'Errore in fase di aggiornamento');
            }
            $this->get('session')->getFlashBag()->add('notice', 'Prodotto aggiornato con successo');
        }
        return $this->redirect($this->generateUrl('acme_store_product_update', array('id' => $id)));      
    }


    /* Show full list of Products */

    public function showAllAction()
    {
        $products = $this->getDoctrine()
                         ->getRepository('AcmeStoreBundle:Product')
                         ->findAllOrderedByName();

        $i = 0;
        foreach ($products as $product) {
            $rows[$i]['id']         = $product->getId();
            $rows[$i]['name']       = $product->getName();
            $rows[$i]['price']      = $product->getPrice();
            $rows[$i]['category']   = $product->getCategory();
            $i ++;
        }

        return $this->render('AcmeStoreBundle:Product:showAll.html.twig', array('rows' => $rows));
    }

    /**
     * Remove Product from display list
     */
    public function removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AcmeStoreBundle:Product')->find($id);

        if (!$product) {
            $this->get('session')->getFlashBag()->add('notice', 'Nessun prodotto con questo Id trovato');
        }

        $em->remove($product);
        $em->flush();

        $this->get('session')->getFlashBag()->add('notice', 'Prodotto eliminato con successo');
    
        return $this->redirect($this->generateUrl('acme_store_show_all'));
    }

    /**
     * Old Methods No use/ To remove
     */
    public function showAction($id)
    {
    	$repository = $this->getDoctrine()->getRepository('AcmeStoreBundle:Product');

        $product = $repository->find($id);

        if (!$product) {
    		return new Response('Nessun prodotto trovato con id ' . $id );
    	} else {
    		return $this->render('AcmeStoreBundle:Default:show.html.twig', 
                    array( 
                        'name' => $product->getName(), 
                        'description' => $product->getDescription(), 
                        'price' => $product->getPrice(),
                        )
                    );
    	}
    }

    /**
     * Update Product 
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AcmeStoreBundle:Product')->find($id);

        if (!$product) {
            return new Response('Nessun prodotto trovato con id ' . $id );
        }

        $product->setName('Kawasaki Z1000 2008');
        $em->flush();

        return new Response('Prodotto Modificato' );
    }

}
