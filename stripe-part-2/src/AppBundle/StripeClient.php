<?php

namespace AppBundle;

use AppBundle\Entity\User;
use AppBundle\Subscription\SubscriptionPlan;
use Doctrine\ORM\EntityManager;
use Exception;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Stripe;

class StripeClient
{
    private $em;

    public function __construct($secretKey, EntityManager $em)
    {
        $this->em = $em;

        Stripe::setApiKey($secretKey);
    }

    public function createCustomer(User $user, $paymentToken)
    {
        $customer = Customer::create([
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
        $customer = Customer::retrieve($user->getStripeCustomerId());

        $customer->source = $paymentToken;
        $customer->save();

        return $customer;
    }

    public function createInvoiceItem($amount, User $user, $description)
    {
        return InvoiceItem::create(array(
            "amount" => $amount,
            "currency" => "eur",
            "customer" => $user->getStripeCustomerId(),
            "description" => $description
        ));
    }

    public function createInvoice(User $user, $payImmediately = true)
    {
        $invoice = Invoice::create(array(
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
     * @param bool $cancel
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     */
    public function cancelSubscription(User $user, $cancel = true) {
        /** @var \Stripe\Subscription $subscription */
        $subscription =  \Stripe\Subscription::update(
            $user->getSubscription()->getStripeSubscriptionId(), [
            array('cancel_at_period_end' => $cancel)
        ]);

        return $subscription;
    }

    /**
     * @param User $user
     * @return \Stripe\Subscription
     * @throws ApiErrorException
     * @throws Exception
     */
    public function reactivateSubscription(User $user) {

        if(!$user->hasActiveSubscription()) {
            throw new Exception("Subscriptions can only be reactivated  if the subscription has not been ended");
        }

        $subscription = $this->cancelSubscription($user, false);

        return $subscription;
    }
}
