<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\StripeClient;
use AppBundle\Subscription\SubscriptionHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stripe\Exception\ApiErrorException;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class ProfileController extends BaseController
{
    /**
     * @Route("/profile", name="profile_account")
     */
    public function accountAction()
    {
        return $this->render('profile/account.html.twig');
    }

    /**
     * @Route("/profile/subscription/cancel", name="account_subscription_cancel")
     * @Method({"POST"})
     */
    public function cancelSubscriptionAction()
    {
        /** @var StripeClient $stripeClient */
        $stripeClient = $this->get('stripe_client');

        $stripeClient->cancelSubscription($this->getUser());

        /** @var Subscription $subscription */
        $subscription = $this->getUser()->getSubscription();

        $subscription->deactivateSubscription();

        $em = $this->getDoctrine()->getManager();
        $em->persist($subscription);
        $em->flush();

        $this->addFlash('success', 'Subscription canceled :(');

        return $this->redirectToRoute('profile_account');
    }

    /**
     * @Route("/profile/subscription/reactivate", name="account_subscription_reactivate")
     * @Method({"POST"})
     * @throws ApiErrorException
     */
    public function reactiveSubscriptionAction()
    {
        /** @var StripeClient $stripeClient */
        $stripeClient = $this->get('stripe_client');

        /** @var Subscription $subscription */
        $subscription = $stripeClient->reactivateSubscription($this->getUser());

        /** @var SubscriptionHelper $subscriptionHelper */
        $subscriptionHelper = $this->get('subscription_helper');

        $subscriptionHelper->addSubscriptionToUser($subscription, $this->getUser());

        $this->addFlash('success', 'Welcome back!');
        return $this->redirectToRoute('profile_account');
    }

}
