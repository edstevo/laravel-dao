<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Repositories;

use EdStevo\Dao\Contracts\DaoCriteria as DaoCriteriaContract;
use EdStevo\Dao\Contracts\DaoBase as DaoBaseContract;
use EdStevo\Dao\Contracts\DaoGenerator as DaoGeneratorContract;
use EdStevo\Dao\Contracts\DaoValidator as DaoValidatorContract;
use EdStevo\Dao\Models\BaseModel;
use EdStevo\Dao\Repositories\DaoCriteria as DaoCriteriaBase;
use EdStevo\Dao\Exceptions\DaoException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

abstract class DaoBase implements DaoCriteriaContract, DaoBaseContract
{

    /**
     * @var \EdStevo\Dao\Models\BaseModel
     */
    protected $model;

    /**
     * @var \EdStevo\Dao\Models\BaseModel
     */
    protected $modelAccess;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Event Dispatch Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Generator Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoGenerator
     */
    protected $generator;

    /**
     * Validator Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoValidator
     */
    protected $validator;

    public function __construct()
    {
        $this->criteria         = collect();
        $this->resetScope();
        $this->makeModel();

        $this->eventDispatcher  = new DaoEventDispatcher($this);
        $this->generator        = new DaoGenerator($this);
        $this->validator        = new DaoValidator($this);
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract protected function model();

    /**
     * Set the model for the repository
     *
     * @return \EdStevo\Dao\Models\BaseModel
     *
     * @throws \EdStevo\Dao\Exceptions\DaoException
     */
    protected function makeModel() : BaseModel
    {
        $model  = resolve($this->model());

        if (!$model instanceof BaseModel)
            throw new DaoException("Class {$this->model()} must be an instance of EdStevo\\Dao\\Models\\BaseModel");

        $this->modelAccess  = $model;
        return $this->model = $model;
    }

    /**
     * Provide direct access to the model
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * Retrieve all entries of the resource from the DB
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all() : Collection
    {
        $this->applyCriteria();
        return $this->model->orderBy($this->getSortField())->get();
    }

    /**
     * Define the primary sorting field for this model
     *
     * @return string
     */
    public function getSortField() : string
    {
        return 'name';
    }

    /**
     * Put a new entry for the resource in the DB
     *
     * @param   array $data
     *
     * @return  \EdStevo\Dao\Models\BaseModel
     */
    public function store(array $data)
    {
        $data   = $this->cleanData($data);

        $model  = $this->model->create($data);

        $this->notify()->created()->with($model)->fire();

        return $model;
    }

    /**
     * Find a current instance or create a new one
     *
     * @param array $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function firstOrCreate(array $data) : BaseModel
    {
        $data       = $this->cleanData($data);

        return $this->model->firstOrCreate($data);
    }

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int  $id
     *
     * @return \EdStevo\Dao\Models\BaseModel|null;
     */
    public function find($id)
    {
        $this->applyCriteria();
        return $this->model->find($id);
    }

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
    public function findOrFail($id) : BaseModel
    {
        $this->applyCriteria();
        $result  = $this->find($id);

        if (!$result)
        {
            return $this->notFound();
        }

        return $result;
    }

    /**
     * Retrieve an entry of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \EdStevo\Dao\Models\BaseModel|null;
     */
    public function findWhere(array $data)
    {
        return $this->model->where($data)->first();
    }

    /**
     * Retrieve multiple entries of the resource from the DB where it matches certain criteria
     *
     * @param  array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function where(array $data) : Collection
    {
        $this->applyCriteria();
        return $this->model->where($data)->get();
    }

    /**
     * Retrieve multiple entries of the resource from the DB where it matches an attribute
     *
     * @param  array  $ids
     * @param  string  $attribute
     *
     * @return \Illuminate\Support\Collection
     */
    public function whereIn(array $ids, string $attribute = null)
    {
        $attribute  = ($attribute) ?: $this->model->getIdField();

        $this->applyCriteria();

        return $this->model->whereIn($attribute, $ids)->get();
    }

    /**
     * Retrieve multiple entries of the resource from the DB where it doesn't matches an attribute
     *
     * @param  array  $ids
     * @param  string  $attribute
     *
     * @return \Illuminate\Support\Collection
     */
    public function whereNotIn(array $ids, string $attribute = null)
    {
        $attribute  = ($attribute) ?: $this->model->getIdField();

        $this->applyCriteria();
        return $this->model->whereNotIn($attribute, $ids)->get();
    }

    /**
     * Update the specified resource in the DB.
     *
     * @param \EdStevo\Dao\Models\BaseModel   $model
     * @param array     $data
     * @param string $attribute
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function update(BaseModel $model, array $data) : BaseModel
    {
        $data       = $this->cleanData($data);

        foreach ($data as $key => $value)
        {
            $model->$key    = $value;
        }

        $model->save();

        $model->update($data);

        $this->notify()->updated()->with($model)->fire();

        return $model;
    }

    /**
     * Increment the specified resource in the DB.
     *
     * @param \EdStevo\Dao\Models\BaseModel $model
     * @param string                $field
     * @param int                   $value
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function increment(BaseModel $model, string $field, int $value = 1) : BaseModel
    {
        $model->increment($field, $value);

        $this->notify()->updated()->with($model)->fire();

        return $model;
    }

    /**
     * Remove an entry for the specified resource from the DB.
     *
     * @param   \EdStevo\Dao\Models\BaseModel   $model
     *
     * @return  boolean
     */
    public function destroy(BaseModel $model) : bool
    {
        $result = $model->delete();

        if ($result)
        {
            $this->notify()->destroyed()->with($model)->fire();
        }

        return $result;
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB
     *
     * @param   BaseModel               $model
     * @param   string                  $relation
     *
     * @return  mixed
     */
    public function getRelation(BaseModel $model, string $relation)
    {
        return $model->$relation;
    }

    /**
     * Retrieve all entries of a resource related to this model from the DB with constraints
     *
     * @param   \EdStevo\Dao\Models\BaseModel        $model
     * @param   string                       $relation
     * @param   array                        $constraints
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    public function getRelationWhere(BaseModel $model, string $relation, array $constraints = []) : Collection
    {
        return $model->$relation()->where($constraints)->get();
    }

    /**
     * Update a relation of the model
     *
     * @param \EdStevo\Dao\Models\BaseModel     $model
     * @param string                    $relationship
     * @param \EdStevo\Dao\Models\BaseModel     $relation
     * @param array                     $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function updateRelation(BaseModel $model, string $relationship, BaseModel $relation, array $data) : BaseModel
    {
        $data       = $this->cleanData($data, $relation);

        if ($model->$relationship() instanceof HasMany)
        {
            $this->updateRelationHasMany($model, $relationship, $relation, $data);
        }

        if ($model->$relationship() instanceof BelongsToMany)
        {
            $this->updateRelationBelongsToMany($model, $relationship, $relation, $data);
        }

        if ($model->$relationship() instanceof MorphMany)
        {
            $this->updateRelationMorphMany($model, $relationship, $relation, $data);
        }

        $updatedRelation    = $this->getRelationWhere($model, $relationship, [$relation->getIdField() => $relation->getId()])->first();

        $this->notify()->updated()->with($model, $updatedRelation)->fire();

        return $updatedRelation;
    }

    /**
     * Put a new resource in storage that is related to another resource
     *
     * @param \EdStevo\Dao\Models\BaseModel    $model
     * @param string                   $relationship
     * @param array                    $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function storeRelation(BaseModel $model, string $relationship, array $data = []) : BaseModel
    {
        $data           = $this->cleanData($data, $model->$relationship()->getRelated());

        if ($model->$relationship() instanceof BelongsTo || $model->$relationship() instanceof BelongsToMany)
        {
            $foreignKey     = $model->$relationship()->getQualifiedForeignKey();

        } else {

            $foreignKey     = $model->$relationship()->getQualifiedForeignKeyName();

            $foreignKey         = explode(".", $foreignKey)[1];
            $data[$foreignKey]  = $model->getId();
        }

        $result     = $model->$relationship()->create($data)->refresh();

        $this->notify()->created()->with($model, $result)->fire();

        return $result;
    }

    /**
     * Update the related model via a has many relationship
     *
     * @param \EdStevo\Dao\Models\BaseModel     $model
     * @param string                    $relationship
     * @param \EdStevo\Dao\Models\BaseModel     $relation
     * @param array                     $data
     *
     * @return bool
     */
    private function updateRelationHasMany(BaseModel $model, string $relationship, BaseModel $relation, array $data) : bool
    {
        return $model->$relationship()->where($relation->getIdField(), $relation->getId())->first()->update($data);
    }

    /**
     * Update the related model via a belongs to many relationship
     *
     * @param \EdStevo\Dao\Models\BaseModel     $model
     * @param string                    $relationship
     * @param \EdStevo\Dao\Models\BaseModel     $relation
     * @param array                     $data
     *
     * @return bool
     */
    private function updateRelationBelongsToMany(BaseModel $model, string $relationship, BaseModel $relation, array $data) : bool
    {
        return $model->$relationship()->where($relation->getIdField(), $relation->getId())->first()->update($data);
    }

    /**
     * Update the related model via a polymorphic relationship
     *
     * @param \EdStevo\Dao\Models\BaseModel     $model
     * @param string                            $relationship
     * @param \EdStevo\Dao\Models\BaseModel     $relation
     * @param array                             $data
     *
     * @return bool
     */
    private function updateRelationMorphMany(BaseModel $model, string $relationship, BaseModel $relation, array $data) : bool
    {
        return $model->$relationship()->where($relation->getIdField(), $relation->getId())->first()->update($data);
    }

    /**
     * Destroy a relation and fire an event attached with this model
     *
     * @param \EdStevo\Dao\Models\BaseModel    $model
     * @param string                           $relationship
     * @param \EdStevo\Dao\Models\BaseModel    $relation
     *
     * @return bool|null
     */
    public function destroyRelation(BaseModel $model, string $relationship, BaseModel $relation)
    {
        $result         = $relation->delete();

        $this->notify()->destroyed()->with($model, $relation)->fire();

        return $result;
    }

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
    public function attach(BaseModel $model, string $relationship, BaseModel $relation, array $pivot_data = [])
    {
        $result         = $model->$relationship()->attach($relation->getId(), $pivot_data);

        $this->notify()->attached()->with($model, $relation)->fire();

        return $result;
    }

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
    public function sync(BaseModel $model, string $relationship, $relation_id, bool $detaching = true)
    {
        return $model->$relationship()->sync($relation_id, $detaching);
    }

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
    public function updatePivot(BaseModel $model, string $relationship, BaseModel $relation, array $pivot_data = [])
    {
        $result         = $model->$relationship()->updateExistingPivot($relation->getId(), $pivot_data);

        $this->notify()->updated()->with($model, $relation)->fire();

        return $result;
    }

    /**
     * Dissociate a model with a relation via a pivot
     *
     * @param   \EdStevo\Dao\Models\BaseModel       $model
     * @param   string                      $relationship
     * @param   \EdStevo\Dao\Models\BaseModel       $relation
     *
     * @param   bool
     */
    public function detach(BaseModel $model, string $relationship, BaseModel $relation) : bool
    {
        $result         = $model->$relationship()->detach($relation->getId());

        $this->notify()->detached()->with($model, $relation)->fire();

        return $result;
    }

    /**
     * Toggle a many to many relationship between these models
     *
     * @param \EdStevo\Dao\Models\BaseModel $model
     * @param string                $relationship
     * @param \EdStevo\Dao\Models\BaseModel $relation
     *
     * @return array
     */
    public function toggle(BaseModel $model, string $relationship, BaseModel $relation) : array
    {
        $result         = $model->$relationship()->toggle($relation->getId());

        $this->notify()->toggled()->with($model, $relation)->fire();

        return $result;
    }

    /**
     * Get the validation rules associated with storing a model
     *
     * @return  array
     */
    public function getStoreRules() : array
    {
        return $this->modelAccess->storeRules();
    }

    /**
     * Get the validation rules associated with storing a model
     *
     * @return  array
     */
    public function getUpdateRules() : array
    {
        return $this->modelAccess->updateRules();
    }

    /**
     * Get the name of the db table for this model
     *
     * @return string
     */
    public function getTable() : string
    {
        return $this->modelAccess->getTable();
    }

    /**
     * Throw exception when model cannot be found
     *
     * @throws  \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function notFound()
    {
        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Reset all criteria on this dao model
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function resetScope() : DaoBaseContract
    {
        $this->skipCriteria(false);

        return $this;
    }

    /**
     * Set this dao model to skip any criteria
     * 
     * @param   bool    $status
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function skipCriteria(bool $status = true) : DaoBaseContract
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * Push criteria in the dao model
     *
     * @param \EdStevo\Dao\Repositories\DaoCriteria $criteria
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function pushCriteria(DaoCriteriaBase $criteria) : DaoBaseContract
    {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * Apply the criteria to the model
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function applyCriteria() : DaoBaseContract
    {
        if($this->skipCriteria === true)
            return $this;

        foreach($this->getCriteria() as $criteria) {
            if($criteria instanceof DaoCriteriaBase)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }

    /**
     * Return the criteria associated with this model
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Clean a data set to only allow the correct data for this model
     *
     * @param array                               $data
     * @param \EdStevo\Dao\Models\BaseModel $model
     *
     * @return array
     */
    private function cleanData(array $data, BaseModel $model = null) : array
    {
        if (is_null($model))
            $model          = $this->model;

        $allowed_fields     = $model->getFillable();
        $data               = collect($data);

        $data               = $data->only($allowed_fields);

        return $data->toArray();
    }

    /**
     * Generate models against this dao
     *
     * @return \EdStevo\Dao\Repositories\DaoEventDispatcher
     */
    private function notify(bool $persist = true) : DaoEventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * Generate models against this dao
     *
     * @return \EdStevo\Dao\Contracts\DaoGenerator
     */
    public function generate(bool $persist = true) : DaoGeneratorContract
    {
        return $this->generator->setPersist($persist);
    }

    /**
     * Validate against this dao
     *
     * @return \EdStevo\Dao\Contracts\DaoValidator
     */
    public function validate(array $rules = []) : DaoValidatorContract
    {
        return $this->validator->setRules($rules);
    }
}