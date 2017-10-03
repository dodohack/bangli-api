<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        /* This defines the polymorphic table *_type and *_id key, see laravel
         * polymorphic table for how this works. */
        Relation::morphMap([
            'post'     => 'App\Models\Post',
            'page'     => 'App\Models\Page',
            'topic'    => 'App\Models\Topic',
            'deal'     => 'App\Models\Deal',
            'newsletter' => 'App\Models\Newsletter',
        ]);
    }
}
