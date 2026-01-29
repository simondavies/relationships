<?php

namespace Stillat\Relationships;

use Illuminate\Support\Facades\Event;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;
use Statamic\Events\TermSaving;
use Statamic\Events\UserDeleted;
use Statamic\Events\UserSaved;
use Statamic\Events\UserSaving;
use Statamic\Providers\AddonServiceProvider;
use Stillat\Relationships\Console\Commands\FillRelationshipsCommand;
use Stillat\Relationships\Console\Commands\ListRelationshipsCommand;
use Stillat\Relationships\Events\EventStack;
use Stillat\Relationships\Listeners\EntryDeletedListener;
use Stillat\Relationships\Listeners\EntrySavedListener;
use Stillat\Relationships\Listeners\EntrySavingListener;
use Stillat\Relationships\Listeners\TermDeletedListener;
use Stillat\Relationships\Listeners\TermSavedListener;
use Stillat\Relationships\Listeners\TermSavingListener;
use Stillat\Relationships\Listeners\UserDeletedListener;
use Stillat\Relationships\Listeners\UserSavedListener;
use Stillat\Relationships\Listeners\UserSavingListener;
use Stillat\Relationships\Processors\RelationshipProcessor;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        FillRelationshipsCommand::class,
        ListRelationshipsCommand::class,
    ];

    public function register()
    {
        $this->app->singleton(EventStack::class, function ($app) {
            return new EventStack();
        });

        $this->app->singleton(RelationshipManager::class, function ($app) {
            return new RelationshipManager($app->make(RelationshipProcessor::class));
        });
    }

    public function boot()
    {
        // Note: parent::boot() is NOT called here to prevent duplicate listener registration in Statamic v6
        // The $listen property doesn't work in v6's AddonServiceProvider, so we manually register listeners
        
        Event::listen(EntrySaving::class, EntrySavingListener::class);
        Event::listen(EntrySaved::class, EntrySavedListener::class);
        Event::listen(EntryDeleted::class, EntryDeletedListener::class);

        Event::listen(UserSaving::class, UserSavingListener::class);
        Event::listen(UserSaved::class, UserSavedListener::class);
        Event::listen(UserDeleted::class, UserDeletedListener::class);

        Event::listen(TermSaving::class, TermSavingListener::class);
        Event::listen(TermSaved::class, TermSavedListener::class);
        Event::listen(TermDeleted::class, TermDeletedListener::class);
    }
}
