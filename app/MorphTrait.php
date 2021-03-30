<?php

namespace App;

trait MorphTrait
{

    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        //customization starts here
        if (isset($attributes['force_class_morph'])) {
            $class = $attributes['force_class_morph'];
            $model = new $class((array)$attributes);
        } else {
            $model = new static((array)$attributes);//this line is from the original source
        }
        //customization ends here

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        $model->mergeCasts($this->casts);

        return $model;
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param array $attributes
     * @param string|null $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $newInstance = [];
        //customization starts here
        if ($this->isValidMorphConfiguration($attributes)) {
            $newInstance = [
                'force_class_morph' => $this->morphMap[$attributes->{$this->morphKey}],
            ];
        }
        //customization ends here

        $model = $this->newInstance($newInstance, true);//minor change here [] for $newIstance

        $model->setRawAttributes((array)$attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Custom logic to determine class morph mapping
     * @param $attributes
     *
     * @return bool
     */
    private function isValidMorphConfiguration($attributes)
    {
        if (!isset($this->morphKey) || empty($this->morphMap)) {
            return false;
        }

        if (!array_key_exists($this->morphKey, (array)$attributes)) {
            return false;
        }

        return array_key_exists($attributes->{$this->morphKey}, $this->morphMap);
    }
}