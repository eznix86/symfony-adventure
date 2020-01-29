<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\StripeClient;
use AppBundle\Subscription\SubscriptionHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Request;

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
        return $this->render('profile/account.html.twig', [
            'error' => null,
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

    /**
     * @Route("/profile/subscription/cancel", name="account_subscription_cancel")
     * @Method({"POST"})
     */
    public function cancelSubscriptionAction()
    {
        /** @var StripeClient $stripeClient */
        $stripeClient = $this->get('stripe_client');

        /** @var Subscription $subscription */
        $subscription = $this->getUser()->getSubscription();


        $stripeSubscription = $stripeClient->cancelSubscription($this->getUser());

        if ($stripeSubscription->status === Subscription::CANCELED) {
            $subscription->cancel();
        } else {
            $subscription->deactivateSubscription();
        }


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

    /**
     * @Route("/profile/card/update", name="account_update_credit_card")
     * @Method({"POST"})
     */
    public function updateCreditCardAction(Request $request)
    {
        $token = $request->request->get('stripeToken');
        $user = $this->getUser();

        try {
            /** @var StripeClient $stripeClient */
            $stripeClient = $this->get('stripe_client');
            $stripeCustomer = $stripeClient->updateCustomerCard($user, $token);
        }catch (\Exception $exception) {
            $this->addFlash('error', 'There was a problem charging your card: '.$exception->getMessage());
            return $this->redirectToRoute('profile_account');
        }

        /** @var SubscriptionHelper $subscriptionHelper */
        $subscriptionHelper = $this->get('subscription_helper');

        $subscriptionHelper->updateCardDetails($user, $stripeCustomer);
        $this->addFlash('success', 'Card Updated');

        return $this->redirectToRoute('profile_account');
    }

}
