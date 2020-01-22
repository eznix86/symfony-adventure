<?php

namespace AppBundle\Subscription;

class SubscriptionHelper
{
    /** @var SubscriptionPlan[] */
    private $plans = [];

    public function __construct()
    {
        $this->plans[] = new SubscriptionPlan(
            'farmer_brent_monthly',
            'Farmer Brent',
            99
        );

        $this->plans[] = new SubscriptionPlan(
            'new_zealander_monthly',
            'New Zealander',
            99
        );
    }

    /**
     * @param $planId
     * @return SubscriptionPlan|null
     */
    public function findPlan($planId)
    {
        foreach ($this->plans as $plan) {
            if ($plan->getPlanId() == $planId) {
                return $plan;
            }
        }
    }
}
