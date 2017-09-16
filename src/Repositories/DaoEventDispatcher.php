<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Repositories;

use EdStevo\Dao\Contracts\DaoEventDispatcher as DaoEventDispatcherContract;
use EdStevo\Dao\Models\BaseModel;

class DaoEventDispatcher implements DaoEventDispatcherContract
{

    /**
     * Dao Base Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoBase
     */
    protected $dao;

    /**
     * Event Type
     *
     * @var string
     */
    protected $eventType;

    /**
     * Primary Model
     *
     * @var \EdStevo\Dao\Models\BaseModel
     */
    protected $primaryModel;

    /**
     * Secondary Model
     *
     * @var \EdStevo\Dao\Models\BaseModel
     */
    protected $relatedModel;

    public function __construct(DaoBase $dao)
    {
        $this->dao      = $dao;
    }

    /**
     * Set the Event Type as Created
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function created() : DaoEventDispatcher
    {
        $this->eventType    = "Created";

        return $this;
    }

    /**
     * Set the Event Type as Updated
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function updated() : DaoEventDispatcher
    {
        $this->eventType    = "Updated";

        return $this;
    }

    /**
     * Set the Event Type as Destroyed
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function destroyed() : DaoEventDispatcher
    {
        $this->eventType    = "Destroyed";

        return $this;
    }

    /**
     * Set the Event Type as Attached
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function attached() : DaoEventDispatcher
    {
        $this->eventType    = "Attached";

        return $this;
    }

    /**
     * Set the Event Type as Toggled
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function toggled() : DaoEventDispatcher
    {
        $this->eventType    = "Toggled";

        return $this;
    }

    /**
     * Set the Event Type as Detached
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function detached() : DaoEventDispatcher
    {
        $this->eventType    = "Detached";

        return $this;
    }

    /**
     * Set the models which will be sent with the event
     *
     * @param \EdStevo\Dao\Models\BaseModel      $primaryModel
     * @param \EdStevo\Dao\Models\BaseModel|NULL $relatedModel
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function with(BaseModel $primaryModel, BaseModel $relatedModel = null) : DaoEventDispatcher
    {
        $this->primaryModel     = $primaryModel;
        $this->relatedModel     = $relatedModel;

        return $this;
    }

    /**
     * Fire the model event
     *
     * @param string                     $eventType
     * @param \EdStevo\Dao\Models\BaseModel      $model
     * @param \EdStevo\Dao\Models\BaseModel|NULL $relation
     *
     * @return void;
     */
    public function fire()
    {
        if (!$this->dao->events || $this->dao->skipEvents)
            return;

        $eventName  = $this->getEventNamespace();

        if(class_exists($eventName))
        {
            broadcast(new $eventName($this->primaryModel, $this->relatedModel))->toOthers();
        }
    }

    /**
     * Get the Class Name from a namespace
     *
     * @param $namespace
     *
     * @return string
     */
    private function getClassName($namespace = null) : string
    {
        $namespace  = (is_null($namespace)) ? get_class($this) : $namespace;

        $namespaceParts = explode("\\", $namespace);
        return collect($namespaceParts)->last();
    }

    /**
     * Get the app namespace
     *
     * @return string
     */
    private function getAppNamespace() : string
    {
        return app()->getNamespace();
    }

    /**
     * Get the events namespace
     *
     * @return string
     */
    private function getEventsNamespace() : string
    {
        return $this->getAppNamespace() . "Events\\Dao\\";
    }

    /**
     * Get the namespace for an event
     *
     * @param string $parentModel
     * @param string $relationModel
     * @param string $event
     *
     * @return string
     */
    private function getEventNamespace() : string
    {
        $modelName      = $this->getClassName($this->primaryModel->getModelName());
        $eventString    = $this->getEventsNamespace() . $modelName;

        if ($this->relatedModel)
        {
            $relatedName    = $this->getClassName($this->relatedModel->getModelName());
            $eventString    = $eventString . "\\" . $relatedName . "\\" . $relatedName;
        } else {
            $eventString    = $eventString . "\\" . $modelName . "\\" . $modelName;
        }

        return $eventString . $this->eventType;
    }
}