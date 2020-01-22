<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    /**
     * @Route("/cart/product/{slug}", name="order_add_product_to_cart")
     * @Method("POST")
     */
    public function addProductToCartAction(Product $product)
    {
        $this->get('shopping_cart')
            ->addProduct($product);

        $this->addFlash('success', 'Product added!');

        return $this->redirectToRoute('order_checkout');
    }

    /**
     * @Route("/checkout", name="order_checkout")
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     * @return Response
     */
    public function checkoutAction(Request $request)
    {
        $products = $this->get('shopping_cart')->getProducts();
        if ($request->isMethod('POST')) {
            $token = $request->get('stripeToken');
            Stripe::setApiKey($this->getParameter('stripe_secret_key'));
            $charge = Charge::create([
                'amount' => $this->get('shopping_cart')->getTotal() * 100,
                'currency' => 'usd',
                'source' => $token,
                'description' => 'first test checkout ',
            ]);
            $this->get('shopping_cart')->emptyCart();
            $this->addFlash('success', 'Order Complete! Yay!');
            return $this->redirectToRoute('homepage');

        }

        return $this->render('order/checkout.html.twig', array(
            'products' => $products,
            'cart' => $this->get('shopping_cart'),
            'stripe_public_key' => $this->getParameter('stripe_public_key')
        ));

    }
}