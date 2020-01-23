<?php

namespace AppBundle;

use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use AppBundle\Subscription\SubscriptionPlan;
use Doctrine\ORM\EntityManager;

class StripeClient
{
    private $em;

    public function __construct($secretKey, EntityManager $em)
    {
        $this->em = $em;

        \Stripe\Stripe::setApiKey($secretKey);
    }

    public function createCustomer(User $user, $paymentToken)
    {
        $customer = \Stripe\Customer::create([
            'email' => $user->getEmail(),
            'source' => $paymentToken,
        ]);
        $user->setStripeCustomerId($customer->id);

        $this->em->persist($user);
        $this->em->flush($user);

        return $customer;
    }

    public function updateCustomerCard(User $user, $paymentToken)
    {
        $customer = \Stripe\Customer::retrieve($user->getStripeCustomerId());

        $customer->source = $paymentToken;
        $customer->save();

        return $customer;
    }

    public function createInvoiceItem($amount, User $user, $description)
    {
        return \Stripe\InvoiceItem::create(array(
            "amount" => $amount,
            "currency" => "eur",
            "customer" => $user->getStripeCustomerId(),
            "description" => $description
        ));
    }

    public function createInvoice(User $user, $payImmediately = true)
    {
        $invoice = \Stripe\Invoice::create(array(
            "customer" => $user->getStripeCustomerId()
        ));

        if ($payImmediately) {
            // guarantee it charges *right* now
            $invoice->pay();
        }

        return $invoice;
    }

    public function createSubscription(User $user, SubscriptionPlan $subscriptionPlan) {
        $subscription =  \Stripe\Subscription::create(array(
            "customer" => $user->getStripeCustomerId(),
            "plan" => $subscriptionPlan->getPlanId()
        ));

        return $subscription;
    }

    /**
     * @param User $user
     * @return \Stripe\Subscription
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function cancelSubscription(User $user) {
        /** @var \Stripe\Subscription $subscription */
        $subscription =  \Stripe\Subscription::update(
            $user->getSubscription()->getStripeSubscriptionId(), [
            array('cancel_at_period_end' => true)
        ]);

        return $subscription;
    }
}
