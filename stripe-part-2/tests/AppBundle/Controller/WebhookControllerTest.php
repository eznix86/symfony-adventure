<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    private $container;
    /** @var EntityManager */
    private $em;


    public function setUp(): void
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testStripeCustomerSubscriptionDeleted()
    {
        $subscription = $this->createSubscription();

        $eventJson = $this->getCustomerSubscriptionDeletedEvent(
            $subscription->getStripeSubscriptionId()
        );

        $client = $this->createClient();

        $client->request(
            'POST',
            'webhooks/stripe',
            [],
            [],
            [],
            $eventJson
        );

        dump($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $subscription = $this->em
            ->getRepository(Subscription::class)
            ->find($subscription->getId());

        $this->assertFalse($subscription->isActive());
    }

    /**
     * @return Subscription
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createSubscription()
    {
        $user = new User();
        $user->setEmail('fluffy'.mt_rand().'@sheep.com');
        $user->setUsername('fluffy'.mt_rand());
        $user->setPlainPassword('baa');

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->activateSubscription(
            'plan_STRIPE_TEST_ABC'.mt_rand(),
            'sub_STRIPE_TEST_XYZ'.mt_rand(),
            new \DateTime('+1 month')
        );

        $this->em->persist($user);
        $this->em->persist($subscription);
        $this->em->flush();

        return $subscription;
    }

    public function getCustomerSubscriptionDeletedEvent($subscriptionId)
    {
        $json = <<<EOF
{
  "created": 1326853478,
  "livemode": false,
  "id": "evt_00000000000000",
  "type": "customer.subscription.deleted",
  "object": "event",
  "request": null,
  "pending_webhooks": 1,
  "api_version": "2019-12-03",
  "data": {
    "object": {
      "id": "%s",
      "object": "subscription",
      "application_fee_percent": null,
      "billing_cycle_anchor": 1580277808,
      "billing_thresholds": null,
      "cancel_at": null,
      "cancel_at_period_end": false,
      "canceled_at": null,
      "collection_method": "charge_automatically",
      "created": 1580277808,
      "current_period_end": 1582956208,
      "current_period_start": 1580277808,
      "customer": "cus_00000000000000",
      "days_until_due": null,
      "default_payment_method": "pm_1G69e7AfgOCGWMwPkW9d0jF5",
      "default_source": null,
      "default_tax_rates": [
      ],
      "discount": null,
      "ended_at": 1580297881,
      "items": {
        "object": "list",
        "data": [
          {
            "id": "si_00000000000000",
            "object": "subscription_item",
            "billing_thresholds": null,
            "created": 1580277809,
            "metadata": {
            },
            "plan": {
              "id": "new_00000000000000",
              "object": "plan",
              "active": true,
              "aggregate_usage": null,
              "amount": 9900,
              "amount_decimal": "9900",
              "billing_scheme": "per_unit",
              "created": 1579716943,
              "currency": "eur",
              "interval": "month",
              "interval_count": 1,
              "livemode": false,
              "metadata": {
              },
              "nickname": "new_zealander_monthly",
              "product": "prod_00000000000000",
              "tiers": null,
              "tiers_mode": null,
              "transform_usage": null,
              "trial_period_days": null,
              "usage_type": "licensed"
            },
            "quantity": 1,
            "subscription": "sub_00000000000000",
            "tax_rates": [
            ]
          }
        ],
        "has_more": false,
        "url": "/v1/subscription_items?subscription=sub_GdQVOGWNtnRCRr"
      },
      "latest_invoice": "in_1G69e8AfgOCGWMwP4yaz3772",
      "livemode": false,
      "metadata": {
      },
      "next_pending_invoice_item_invoice": null,
      "pending_invoice_item_interval": null,
      "pending_setup_intent": null,
      "pending_update": null,
      "plan": {
        "id": "new_00000000000000",
        "object": "plan",
        "active": true,
        "aggregate_usage": null,
        "amount": 9900,
        "amount_decimal": "9900",
        "billing_scheme": "per_unit",
        "created": 1579716943,
        "currency": "eur",
        "interval": "month",
        "interval_count": 1,
        "livemode": false,
        "metadata": {
        },
        "nickname": "new_zealander_monthly",
        "product": "prod_00000000000000",
        "tiers": null,
        "tiers_mode": null,
        "transform_usage": null,
        "trial_period_days": null,
        "usage_type": "licensed"
      },
      "quantity": 1,
      "schedule": null,
      "start_date": 1580277808,
      "status": "canceled",
      "tax_percent": null,
      "trial_end": null,
      "trial_start": null
    }
  }
}
EOF;

        return sprintf($json, $subscriptionId);
    }
}
