<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Subscription;
use AppBundle\StripeClient;
use AppBundle\Subscription\SubscriptionHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends BaseController
{
    /**
     * @Route("/webhooks/stripe", name="webhook_stripe")
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function stripeWebhookAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null)
        {
            throw new \Exception("Bad JSON from Stripe");
        }

        $eventId = $data['id'];
        /** @var StripeClient $stripeClient */
        $stripeClient = $this->get('stripe_client');

        if ($this->getParameter('verify_stripe_event')) {
            $stripeEvent = $stripeClient
                ->findEvent($eventId);
        } else {
            // fake the Stripe_Event in the test environment
            $stripeEvent = json_decode($request->getContent());
        }

        switch ($stripeEvent->type) {
            case 'customer.subscription.deleted':
                $subscriptionId = $stripeEvent->data->object->id;
                $subscription = $this->findSubscription($subscriptionId);

                /** @var SubscriptionHelper $subscriptionHelper */
                $subscriptionHelper = $this->get('subscription_helper');

                /** @var Subscription $subscription */
                $subscriptionHelper->fullyCancelSubscription($subscription);

                break;
            default:
                throw new \Exception('Unexpected webhook from stripe: '.$stripeEvent->type);
        }

        return new Response("Event handled: ".$stripeEvent->type);
    }

    private function findSubscription($subscriptionId)
    {
        $subscription = $this->getDoctrine()->getRepository(Subscription::class)
                    ->findOneBy(
                        ["stripeSubscriptionId" => $subscriptionId]
                    );

        if(!$subscription) {
            throw new \Exception("Somehow we have no subscription id ".$subscriptionId);
        }

        return $subscription;
    }
}
