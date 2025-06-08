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
    public ?DateTime $createdAt = null;

    /**
     * The date and time when the model was last updated.
     * This will be automatically updated on every save.
     */
    #[Column]
    public ?DateTime $updatedAt = null;

    /**
     * Hook method to set timestamps before creating a model.
     *
     * This method is automatically called before a new model is created.
     * It sets both the createdAt and updatedAt timestamps to the current date and time.
     *
     * @param Model $model The model instance being created.
     */
    #[Hook('beforeCreate')]
    protected function createTimestamps(Model $model)
    {
        $now = new DateTime();
        $model->createdAt = $now;
        $model->updatedAt = $now;
    }

    /**
     * Hook method to update the updatedAt timestamp before updating a model.
     *
     * This method is automatically called before an existing model is updated.
     * It updates the updatedAt timestamp to the current date and time.
     *
     * @param Model $model The model instance being updated.
     */
    #[Hook('beforeUpdate')]
    protected function updateTimestamps(Model $model)
    {
        $model->updatedAt = new DateTime();
    }

    /**
     * Clears timestamp fields when the model is cloned.
     *
     * This hook is triggered on model cloning to reset the `createdAt` and `updatedAt` fields,
     * ensuring the cloned instance does not retain timestamps from the original.
     *
     * @param Model $model The cloned model instance whose timestamps should be cleared.
     * @return void
     */
    #[Hook('onClone')]
    protected function clearTimestampsOnClone(Model $model)
    {
        // Clear timestamps when cloning a model
        $model->createdAt = null;
        $model->updatedAt = null;
    }
}