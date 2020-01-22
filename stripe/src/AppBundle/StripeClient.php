<?php


namespace AppBundle;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\InvoiceItem;
use Stripe\Stripe;

class StripeClient
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct($secretKey, EntityManager $em)
    {
        $this->em = $em;
        Stripe::setApiKey($secretKey);
    }

    /**
     * @param User $user
     * @param $paymentToken
     * @return Customer
     * @throws ApiErrorException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createCustomer(User $user, $paymentToken) {
        $customer = Customer::create([
            'source' => $paymentToken,
            'email' => $user->getEmail(),
            'description' => 'Customer test@test.com'
        ]);

        $user->setStripeCustomerId($customer->id);

        $this->em->persist($user);
        $this->em->flush();

        return $customer;
    }

    /**
     * @param User $user
     * @param $paymentToken
     * @throws ApiErrorException
     */
    public function updateCustomerCard(User $user, $paymentToken) {
        $customer = Customer::retrieve($user->getStripeCustomerId());
        $customer->source = $paymentToken;
        $customer->save();
    }

    /**
     * @param $amount
     * @param User $user
     * @param $description
     * @return InvoiceItem
     * @throws ApiErrorException
     */
    public function createInvoiceItem($amount, User $user, $description)
    {
        return InvoiceItem::create(array(
            "amount" => $amount,
            "currency" => "usd",
            "customer" => $user->getStripeCustomerId(),
            "description" => $description
        ));
    }

    /**
     * @param User $user
     * @param bool $payImmediately
     * @return Invoice
     * @throws ApiErrorException
     */
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
}
