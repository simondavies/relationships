<?php

namespace Stillat\Relationships\Processors\Concerns;

use Stillat\Relationships\Comparisons\ComparisonResult;
use Stillat\Relationships\EntryRelationship;

trait ProcessesManyToOne
{
    protected function processManyToOne(ComparisonResult $results, EntryRelationship $relationship)
    {
        foreach ($results->removed as $removedId) {
            if ($this->shouldProcessRelationship($relationship, $removedId)) {
                $this->removeFieldValue($relationship, $this->getEffectedEntity($relationship, $removedId));
            }
        }

        // For many-to-one, use setFieldValue (replace) not addItemToEntry (append)
        foreach ($results->added as $addedId) {
            if ($this->shouldProcessRelationship($relationship, $addedId)) {
                $entity = $this->getEffectedEntity($relationship, $addedId);
                
                // Get the current/old value from the entity before we change it
                $oldValue = $entity->get($relationship->rightField);
                
                // If there was a previous relationship, remove this entity from it
                if ($oldValue && $oldValue != $this->entryId) {
                    $oldRelatedEntity = \Statamic\Facades\Entry::find($oldValue);
                    if ($oldRelatedEntity) {
                        // Remove the current entity from the old related entity's collection
                        $oldCollection = $oldRelatedEntity->get($relationship->leftField, []);
                        if (!is_array($oldCollection)) {
                            $oldCollection = $oldCollection ? [$oldCollection] : [];
                        }
                        
                        // Remove the entity ID from the array
                        $oldCollection = array_filter($oldCollection, function($id) use ($addedId) {
                            return $id != $addedId;
                        });
                        
                        $oldRelatedEntity->set($relationship->leftField, array_values($oldCollection));
                        $oldRelatedEntity->saveQuietly();
                    }
                }
                
                // Now set the new relationship
                $this->setFieldValue($relationship, $entity);
            }
        }
    }
}
