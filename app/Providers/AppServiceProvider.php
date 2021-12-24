<?php

namespace App\Providers;

use App\Billings;
use App\Mail\EmailVerification;
use App\DocumentationRequests;
use App\Observers\BillingObserver;
use App\Observers\ProjectsObserver;
use App\Projects;
use Illuminate\Support\ServiceProvider;
use App\User;
use App\Observers\UserObserver;
use App\RegistrationForms;
use App\Observers\RegistrationFormsObserver;
use App\Observers\DocumentationRequestsObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        RegistrationForms::observe(RegistrationFormsObserver::class);
        DocumentationRequests::observe(DocumentationRequestsObserver::class);
        Projects::observe(ProjectsObserver::class);
        Billings::observe(BillingObserver::class);
    }
}
