<?php

namespace EdStevo\Dao\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseModel extends Model
{

    /**
     * Define the validation rules for storing this model
     *
     * @return array
     */
    abstract public function storeRules() : array;

    /**
     * Define the validation rules for updating this model
     *
     * @return array
     */
    abstract public function updateRules() : array;

    /**
     * Get the appends data for this model
     *
     * @return array
     */
    public function getAppends()
    {
        return $this->appends;
    }

    /**
     * Get the identifier for this model
     *
     * @return mixed
     */
    public function getId()
    {
        return $this[$this->primaryKey];
    }

    /**
     * Get the visual identifier for this model
     *
     * @return mixed
     */
    public function getVisualId()
    {
        return $this->getId();
    }

    /**
     * Get the field used as an identifier for this model
     *
     * @return string
     */
    public function getIdField() : string
    {
        return $this->primaryKey;
    }

    /**
     * Expressive way to use the destroy method via dao repository
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function daoUpdate(array $data = []) : BaseModel
    {
        $this->getDaoRepository()->update($this, $data);

        return $this;
    }

    /**
     * Expressive way to use the destroy method via dao repository
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function daoIncrement(string $field = "", int $value = 1) : BaseModel
    {
        $this->getDaoRepository()->increment($this, $field, $value);

        return $this;
    }

    /**
     * Expressive way to use the destroy method via dao repository
     *
     * @return bool
     */
    public function daoDestroy() : bool
    {
        return $this->getDaoRepository()->destroy($this);
    }

    /**
     * Expressive way to use the get relation method with this model via the dao repository
     *
     * @param string $relation
     *
     * @return mixed
     */
    public function daoGetRelation(string $relation)
    {
        return $this->getDaoRepository()->getRelation($this, $relation);
    }

    /**
     * Expressive way to use the get relation where method with this model via the dao repository
     *
     * @param string $relation
     * @param array  $constraints
     *
     * @return \Illuminate\Support\Collection
     */
    public function daoGetRelationWhere(string $relation, array $constraints = []) : Collection
    {
        return $this->getDaoRepository()->getRelationWhere($this, $relation, $constraints);
    }

    /**
     * Expressive way to use the store relation method with this model via the dao repository
     *
     * @param string $relation
     * @param array  $data
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function daoStoreRelation(string $relation, array $data = []) : BaseModel
    {
        return $this->getDaoRepository()->storeRelation($this, $relation, $data);
    }

    /**
     * Expressive way to use the update relation method with this model via the dao repository
     *
     * @param string $relationship
     * @param \EdStevo\Dao\Repositories\BaseModel $relation
     * @param array  $data
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function daoUpdateRelation(string $relationship, BaseModel $relation, array $data = []) : BaseModel
    {
        return $this->getDaoRepository()->updateRelation($this, $relationship, $relation, $data);
    }

    /**
     * Expressive way to use the update pivot method with this model via the dao repository
     *
     * @param string $relationship
     * @param \EdStevo\Dao\Repositories\BaseModel $relation
     * @param array  $data
     *
     * @return int
     */
    public function daoUpdatePivot(string $relationship, BaseModel $relation, array $data = []) : int
    {
        return $this->getDaoRepository()->updatePivot($this, $relationship, $relation, $data);
    }

    /**
     * Expressive way to use the destroy relation method with this model via the dao repository
     *
     * @param string                           $relationship
     * @param \EdStevo\Dao\Repositories\BaseModel $relation
     *
     * @return bool
     */
    public function daoDestroyRelation(string $relationship, BaseModel $relation) : bool
    {
        return $this->getDaoRepository()->destroyRelation($this, $relationship, $relation);
    }

    /**
     * Expressive way to use the attach method with this model via the dao repository
     *
     * @param string                              $relation
     * @param \EdStevo\Dao\Repositories\BaseModel $model
     * @param array                               $pivotData
     */
    public function daoAttach(string $relation, Model $model, array $pivotData = [])
    {
        return $this->getDaoRepository()->attach($this, $relation, $model, $pivotData);
    }

    /**
     * Expressive way to use the sync method with this model via the dao repository
     *
     * @param string $relationship
     * @param        $relation_id
     * @param bool   $detaching
     *
     * @return mixed
     */
    public function daoSync(string $relationship, $relation_id, bool $detaching = true)
    {
        return $this->getDaoRepository()->sync($this, $relationship, $relation_id, $detaching);
    }

    /**
     * Expressive way to use the toggle method with this model via the dao repository
     *
     * @param        $model
     * @param string $relationship
     * @param        $relation
     *
     * @return array
     */
    public function daoToggle(string $relationship, $relation) : array
    {
        return $this->getDaoRepository()->toggle($this, $relationship, $relation);
    }

    /**
     * Expressive way to use the detach method with this model via the dao repository
     *
     * @param        $model
     * @param string $relationship
     * @param        $relation
     *
     * @return bool
     */
    public function daoDetach(string $relationship, $relation) : bool
    {
        return $this->getDaoRepository()->detach($this, $relationship, $relation);
    }

    /**
     * Expressive way to use the get rules method with this model via the dao repository
     *
     * @return array
     */
    public function getRules() : array
    {
        return $this->getDaoRepository()->getRules();
    }

    /**
     * Return the dao repository related to this model
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function getDaoRepository()
    {
        return resolve('App\\Contracts\\Dao\\' . $this->getModelName());
    }

    /**
     * Flush this model's dao's cache
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function flushDaoCache() : BaseModel
    {
        $this->getDaoRepository()->flushCache();

        return $this;
    }

    /**
     * Flush this model's dao's cache
     *
     * @return \EdStevo\Dao\Repositories\BaseModel
     */
    public function flushModelCache() : BaseModel
    {
        $this->getDaoRepository()->flushSingeCache($this->getId());

        return $this;
    }

    /**
     * Get the name of this model
     *
     * @return string
     */
    private function getModelName() : string
    {
        return collect(explode('\\', get_class($this)))->last();
    }

    /**
     * Execute the casting
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function castField($key, $value)
    {
        return $this->castAttribute($key, $value);
    }

    /**
     * Check if a field is set to be casted
     *
     * @param $key
     *
     * @return bool
     */
    public function hasCastValue($key)
    {
        return $this->hasCast($key);
    }
}