<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        Schema::defaultStringLength(255);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('valid_date_range', function ($attribute, $value, $parameters, $validator) {
            $dateBeginning = Carbon::parse(request($parameters[0]));
            $dateEnd = Carbon::parse($value);
            $minDifference = (int)$parameters[1];

            return $dateBeginning->diffInMonths($dateEnd) <= $minDifference;
        });

        if(config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
