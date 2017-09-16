<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Contracts;

use EdStevo\Dao\Models\BaseModel;
use EdStevo\Dao\Repositories\DaoEventDispatcher as DaoEventDispatcherRepo;

interface DaoEventDispatcher
{

    /**
     * Set the Event Type as Created
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function created() : DaoEventDispatcherRepo;

    /**
     * Set the Event Type as Updated
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function updated() : DaoEventDispatcherRepo;

    /**
     * Set the Event Type as Destroyed
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function destroyed() : DaoEventDispatcherRepo;
    /**
     * Set the Event Type as Attached
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function attached() : DaoEventDispatcherRepo;

    /**
     * Set the Event Type as Toggled
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function toggled() : DaoEventDispatcherRepo;
    /**
     * Set the Event Type as Detached
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function detached() : DaoEventDispatcherRepo;

    /**
     * Set the models which will be sent with the event
     *
     * @param \EdStevo\Dao\Models\BaseModel         $primaryModel
     * @param \EdStevo\Dao\Models\BaseModel|NULL    $relatedModel
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    public function with(BaseModel $primaryModel, BaseModel $relatedModel = null) : DaoEventDispatcherRepo;

    /**
     * Fire the model event
     *
     * @param string                                $eventType
     * @param \EdStevo\Dao\Models\BaseModel         $model
     * @param \EdStevo\Dao\Models\BaseModel|NULL    $relation
     *
     * @return void;
     */
    public function fire();

}