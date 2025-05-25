<?php

namespace Potager\Limpid\Traits;

use Potager\Limpid\Attributes\Column;
use Potager\Limpid\Attributes\Hook;
use Potager\Limpid\Model;
use DateTime;

/**
 * Trait WithTimestamps
 *
 * Automatically handles createdAt and updatedAt timestamp columns
 * on models that use this trait.
 */
trait WithTimestamps
{
    /**
     * The date and time when the model was first created.
     * This will be automatically set on initial save.
     */
    #[Column]
    public DateTime $createdAt;

    /**
     * The date and time when the model was last updated.
     * This will be automatically updated on every save.
     */
    #[Column]
    public DateTime $updatedAt;

    /**
     * Hook triggered before saving the model to update timestamps.
     *
     * - If the model is new (not persisted), sets createdAt and updatedAt.
     * - If the model already exists, only updates updatedAt.
     *
     * @param Model $model The model being saved (injected via the hook system)
     */
    #[Hook("beforeSave")]
    protected function setTimestamps(Model $model)
    {
        if (!$model->isPersisted()) {
            // New record: set creation timestamp
            $model->createdAt = new DateTime();
        }

        // Always update updatedAt, even for existing records
        $model->updatedAt = new DateTime();
    }
}