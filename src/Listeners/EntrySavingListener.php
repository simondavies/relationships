<?php

namespace Stillat\Relationships\Listeners;

use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Entries\Entry;
use Statamic\Events\EntrySaving;
use Stillat\Relationships\RelationshipManager;
use Stillat\Relationships\Support\Facades\EventStack;

class EntrySavingListener extends BaseListener
{
    /**
     * @var RelationshipManager
     */
    protected $manager;

    /**
     * @var EntryRepository
     */
    protected $entries;

    public function __construct(RelationshipManager $manager, EntryRepository $entries)
    {
        $this->manager = $manager;
        $this->entries = $entries;
    }

    public function handle(EntrySaving $event)
    {
        EventStack::increment();

        /** @var Entry $entry */
        $entry = $event->entry;
        
        // Convert database model to Entry object FIRST (before calling any methods)
        if (method_exists($entry, 'toEntry')) {
            $entry = $entry->toEntry();
        }
        
        $collection = $entry->collectionHandle();

        if (! $this->manager->hasRelationshipsForCollection($collection)) {
            return;
        }

        if (EventStack::count() > 1 || $this->manager->processor()->isProcessingRelationships()) {
            return;
        }

        $isUpdating = $entry->id() !== null;

        if ($isUpdating) {
            // Use Statamic Facade to properly load entry with database driver
            $foundEntry = \Statamic\Facades\Entry::find($entry->id());

            if ($foundEntry === null) {
                $isUpdating = false;
            } else {
                $entry = clone $foundEntry;
                $isUpdating = true;
            }
        }

        $this->manager->processor()->setIsDeleting(false)->setPristineDetails($entry, ! $isUpdating);
    }
}
