<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    const BASIC_PLAN_PRICE_ID = 'price_1IX7ZLIDrSBqJwGBbangGo2B';
    const PREMIUM_PLAN_PRICE_ID = 'price_1IX7XNIDrSBqJwGBJ6ONOkJq';

    public function settings()
    {
        $user = Auth::user();
        $subscription = $user->subscription('default');
        $subscriptionData = false;
        if ($subscription !== null) {
            $plan = ($subscription->stripe_plan == self::BASIC_PLAN_PRICE_ID)? 'Basic Plan' : 'Premium Plan';
            $price = ($subscription->stripe_plan == self::BASIC_PLAN_PRICE_ID)? 'USD 5.00' : 'USD 7.00';
            $subscriptionData = [
                'status' => $subscription->stripe_status,
                'plan' => $plan,
                'price' => $price,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at
            ];
        }
        return view('subscription.settings', [
            'user' => $user,
            'subscription' => $subscriptionData
        ]);
    }
}
