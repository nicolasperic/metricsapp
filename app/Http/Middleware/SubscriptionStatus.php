<?php

namespace App\Http\Middleware;

use App\Helper\SessionMessage;
use Closure;
use Illuminate\Http\Request;

class SubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        //do not use $user->subscribed('default') it gets the description without returning it..
        //better do extra validations based on subscription status
        $subscription = $user->subscription('default');//if no subscription exists null will received

        //valid() > active(), onTrial, onGracePeriod
        //TODO valid() ya revisa stripe_status
        if (! $subscription || ! $subscription->valid()) {
            //status incomplete fue antes de que se procese el pago!!!
            //> incomplete es el status donde le tengo que decir al customer, esperando confirmación del pago
            //
            //use this to determine customer has a payment method
            $hasPaymentMethod = $user->hasDefaultPaymentMethod();//qué diff con hasPaymentMethod
            //$user->hasIncompletePayment('default')

            //different scenarios
            //1. user trial has ended and he never added a payment method
            //TRIALING > ACTIVE > PAST_DUE
            //status will go from trialing to active when trial ends
            //then after the first invoice that fails the status will go to past_due
            //need to validate what will happen and how long will the customer be in this state.
            //without a valida payment customer shouldn't have access to the app
            //2. user has canceled the subscription (active > cancelled status)
            //ACTIVE but Cancelled2.1 ends_at is set, subscription will be active until that date > cancelled
            //Cancelled 2.2 the status has gone to cancelled



        //if(!$user->onTrial() && !$user->subscribed('default')) {
            SessionMessage::warningMessage('Your trial period has come to an end. Please fill in your payment method to continue using the application.');
            return redirect()->route('subscription.index');
        }

        return $next($request);
    }
}
