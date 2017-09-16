<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Contracts;

use EdStevo\Dao\Contracts\DaoGenerator as DaoGeneratorContract;
use EdStevo\Dao\Contracts\DaoValidator as DaoValidatorContract;
use EdStevo\Dao\Models\BaseModel;
use Illuminate\Support\Collection;

interface DaoBase
{

    /**
     * Provide direct access to the model
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function getModel();

    /**
     * Retrieve all entries of the resource from the DB
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Define the primary sorting field for this model
     *
     * @return string
     */
    public function getSortField() : string;

    /**
     * Put a new entry for the resource in the DB
     *
     * @param   array $data
     *
     * @return  \EdStevo\Dao\Models\BaseModel
     */
    public function store(array $data);

    /**
     * Find a current instance or create a new one
     *
     * @param array $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function firstOrCreate(array $data) : BaseModel;

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int $id
     *
     * @return \EdStevo\Dao\Models\BaseModel|null
     */
    public function find($id);

    /**
     * Retrieve an entry of the resource from the DB
     * If the resource cannot be found, throw ModelNotFoundException
     *
     * @param  int  $id
     *
     * @return \EdStevo\Dao\Models\BaseModel
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id) : BaseModel;

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \EdStevo\Dao\Models\BaseModel|null;
     */
    public function findWhere(array $data);

    /**
     * Retrieve multiple entries of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where(array $data) : Collection;

    /**
     * Retrieve multiple entries of the resource from the DB where it matches an attribute
     *
     * @param  array  $ids
     * @param  string  $attribute
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function whereIn(array $ids, string $attribute = null);

    /**
     * Update the specified resource in the DB.
     *
     * @param \EdStevo\Dao\Models\BaseModel   $model
     * @param array     $data
     * @param string $attribute
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function update(BaseModel $model, array $data) : BaseModel;

    /**
     * Increment the specified resource in the DB.
     *
     * @param \EdStevo\Dao\Models\BaseModel $model
     * @param string                $field
     * @param int                   $value
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function increment(BaseModel $model, string $field, int $value = 1) : BaseModel;

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \EdStevo\Dao\Models\BaseModel   $model
     *
     * @return  boolean
     */
    public function destroy(BaseModel $model) : bool;

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   BaseModel               $model
     * @param   string                  $relation
     *
     * @return  mixed
     */
    public function getRelation(BaseModel $model, string $relation);

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \EdStevo\Dao\Models\BaseModel        $model
     * @param   string                       $relation
     * @param   array                        $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere(BaseModel $model, string $relation, array $constraints = []) : Collection;

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param \EdStevo\Dao\Models\BaseModel    $model
     * @param string                   $relationship
     * @param array                    $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function storeRelation(BaseModel $model, string $relationship, array $data = []) : BaseModel;

    /**
     * Update a relation of the model
     *
     * @param \EdStevo\Dao\Models\BaseModel     $model
     * @param string                    $relationship
     * @param \EdStevo\Dao\Models\BaseModel     $relation
     * @param array                     $data
     *
     * @return \EdStevo\Dao\Repositories\DaoBase
     */
    public function updateRelation(BaseModel $model, string $relationship, BaseModel $relation, array $data) : BaseModel;

    /**
     * Destroy a relation and fire an event attached with this model
     *
     * @param \EdStevo\Dao\Repositories\DaoBase    $model
     * @param string                           $relationship
     * @param \EdStevo\Dao\Repositories\DaoBase    $relation
     *
     * @return bool|null
     */
    public function destroyRelation(BaseModel $model, string $relationship, BaseModel $relation);

    /**
     * Associate a model with a relation via a pivot
     *
     * @param   \EdStevo\Dao\Models\BaseModel       $model
     * @param   string                      $relationship
     * @param   \EdStevo\Dao\Models\BaseModel       $relation
     * @param   array                       $pivot_data
     *
     * @param   null
     */
    public function attach(BaseModel $model, string $relationship, BaseModel $relation, array $pivot_data = []);

    /**
     * Sync a model and its relations via a pivot
     *
     * @param   \EdStevo\Dao\Models\BaseModel       $model
     * @param   string                      $relation
     * @param   int/string                  $relation_id
     * @param   bool                        $detaching
     *
     * @param   array
     */
    public function sync(BaseModel $model, string $relationship, $relation_id, bool $detaching = true);

    /**
     * Update a pivot table across a many to many relationship
     *
     * @param \EdStevo\Dao\Models\BaseModel $model
     * @param string                $relationship
     * @param \EdStevo\Dao\Models\BaseModel $relation
     * @param array                 $pivot_data
     *
     * @return mixed
     */
    public function updatePivot(BaseModel $model, string $relationship, BaseModel $relation, array $pivot_data = []);

    /**
     * Dissociate a model with a relation via a pivot
     *
     * @param   \EdStevo\Dao\Models\BaseModel       $model
     * @param   string                      $relationship
     * @param   \EdStevo\Dao\Models\BaseModel       $relation
     *
     * @param   bool
     */
    public function detach(BaseModel $model, string $relationship, BaseModel $relation) : bool;

    /**
     * Toggle a many to many relationship between these models
     *
     * @param \EdStevo\Dao\Models\BaseModel $model
     * @param string                $relationship
     * @param \EdStevo\Dao\Models\BaseModel $relation
     *
     * @return array
     */
    public function toggle(BaseModel $model, string $relationship, BaseModel $relation) : array;

    /**
     * Get the validation rules associated with storing a model
     *
     * @return  array
     */
    public function getStoreRules() : array;

    /**
     * Get the validation rules associated with updating a model
     *
     * @return  array
     */
    public function getUpdateRules() : array;

    /**
     * Get the name of the db table for this model
     *
     * @return string
     */
    public function getTable() : string;

    /**
     * Generate models against this dao
     *
     * @return \EdStevo\Dao\Contracts\DaoGenerator
     */
    public function generate(bool $persist = true) : DaoGeneratorContract;

    /**
     * Validate against this dao
     *
     * @return \EdStevo\Dao\Contracts\DaoValidator
     */
    public function validate(array $rules = []) : DaoValidatorContract;

}