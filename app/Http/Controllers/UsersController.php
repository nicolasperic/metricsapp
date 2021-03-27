<?php

namespace App\Http\Controllers;

use App\Helper\SessionMessage;
use App\Importer\UserImporter;
use App\Jobs\SyncSpaceUsers;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\BillingPortal\Session;
use Stripe\Stripe;


class UsersController extends Controller
{
     //TODO move this under Controllers/Assembla ProjectsController syncUsers
    public function syncUsers($wikiname)
    {
        $project = Auth::user()->projects()->where('wikiname', $wikiname)->firstorFail();

        try {
            SyncSpaceUsers::dispatch(Auth::user(), $project);
            SessionMessage::infoMessage('Users sync job was added to the queue');
        } catch (\Exception $e) {
            SessionMessage::errorMessage('Oops something went wrong when contacting Assembla, please try again later. If the problem persists contact support.');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return redirect()->route('projects.show', $project->wikiname);
    }

    public function notifications()
    {
        return Auth::user()->unreadNotifications()->limit(5)->get()->toArray();
    }

    public function markNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->map(function($notification) {
            $notification->markAsRead();
        });

        return response()->json(['result' => 'success']);
    }

    /**
     * TODO move this function into a more related controller
     * This function redirects the customer to their Stripe customer portal
     * They will be able to update their payment method and subscription plan
     */
    public function customerPortal()
    {
        $user = Auth::user();
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Authenticate your user.
        $session = Session::create([
            'customer' => $user->stripe_id,
            'return_url' => 'https://example.com/account',
        ]);

    // Redirect to the customer portal.
        header("Location: " . $session->url);
        exit();
    }

    // TODO move this function into a more related controller
    public function checkoutPortal()
    {
        $premiumPlan = Auth::user()
            ->newSubscription('default', 'price_1IX7XNIDrSBqJwGBJ6ONOkJq')
            ->trialDays(env('STRIPE_TRIAL_DAYS', 7))
            ->checkout();

        $basicPlan = Auth::user()
            ->newSubscription('default', 'price_1IX7ZLIDrSBqJwGBbangGo2B')
            ->trialDays(env('STRIPE_TRIAL_DAYS', 7))
            ->checkout();

        //$premiumPlan = 'something';
        //$basicPlan = 'another';
        return view('subscription.products', [
            'premium' => $premiumPlan,
            'basic' => $basicPlan,
        ]);


        //prod_J9QOK9h0UUx3LA
        // Set your secret key. Remember to switch to your live secret key in production.
// See your keys here: https://dashboard.stripe.com/account/apikeys
       /* \Stripe\Stripe::setApiKey('sk_test_51IWOXXIDrSBqJwGBwC6I4t2yRQmII0Lu9zlEv65zm9lgEDFetuF1WUgzB99mHflbtFuWUSmgYW2fvC9cTLOma7NV00pBxEusX2');


            try {
                // See https://stripe.com/docs/api/checkout/sessions/create
                // for additional parameters to pass.
                // {CHECKOUT_SESSION_ID} is a string literal; do not change it!
                // the actual Session ID is returned in the query parameter when your customer
                // is redirected to the success page.
                $checkout_session = \Stripe\Checkout\Session::create([
                    'success_url' => 'https://example.com/success.html?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => 'https://example.com/canceled.html',
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'line_items' => [[
                        'price' => 'prod_J9QOK9h0UUx3LA',
                        // For metered billing, do not pass quantity
                        'quantity' => 1,
                    ]],
                ]);
            } catch (Exception $e) {

            }*/

    }
}
