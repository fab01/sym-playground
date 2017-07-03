<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 10/06/17
 * Time: 13:59
 */

namespace Acme\StoreBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class TestController
{
    /**
     * @Route("/test")
     */
    public function showAction()
    {
        return new Response('Under the sea!');
    }
}