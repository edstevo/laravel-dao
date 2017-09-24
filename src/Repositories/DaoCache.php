<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Repositories;

use EdStevo\Dao\Contracts\DaoBase as DaoBaseContract;
use EdStevo\Dao\Contracts\DaoCache as DaoCacheContract;
use EdStevo\Dao\Contracts\DaoGenerator as DaoGeneratorContract;
use EdStevo\Dao\Contracts\DaoValidator as DaoValidatorContract;
use EdStevo\Dao\Models\BaseModel;
use EdStevo\Dao\Repositories\DaoBase as DaoBaseRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;

abstract class DaoCache implements DaoBaseContract, DaoCacheContract
{

    /**
     * Dao Base Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoBase
     */
    protected $dao;

    /**
     * Cache
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * Array of tags for cache
     *
     * @var \Illuminate\Support\Collection
     */
    protected $tags;

    /**
     * Cache tag for all parts
     *
     * @var string
     */
    protected $listCacheTag = 'all';

    public function __construct(DaoBaseRepository $daoRepository)
    {
        $this->dao      = $daoRepository;
        $this->cache    = resolve(CacheManager::class);
        $this->tags     = collect()->push($this->getBaseTag());
    }

    /**
     * Provide direct access to the model
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function getModel() {
        return $this->dao->getModel();
    }

    /**
     * Retrieve all entries of the resource from the DB
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all() {
        return $this->cacheRequest([$this->listCacheTag]);
    }

    /**
     * Define the primary sorting field for this model
     *
     * @return string
     */
    public function getSortField() : string
    {
        return $this->dao->getSortField();
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
        $result = $this->dao->store($data);

        $this->flushListCache();

        return $result;
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
        return $this->dao->firstOrCreate($data);
    }

    /**
     * Retrieve an entry of the resource from the DB by its ID
     *
     * @param  int $id
     *
     * @return \EdStevo\Dao\Models\BaseModel|null
     */
    public function find($id)
    {
        return $this->dao->find($id);
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
        return $this->dao->findOrFail($id);
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
        return $this->dao->findWhere($data);
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
        return $this->dao->where($data);
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
        return $this->dao->whereIn($ids, $attribute);
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
        $result = $this->dao->update($model, $data);

        $model->flushDaoCache();
        $model->flushModelCache();

        return $result;
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
        $result = $this->dao->increment($model, $field, $value);

        $model->flushDaoCache();
        $model->flushModelCache();

        return $result;
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
        $result = $this->dao->destroy($model);

        $model->flushDaoCache();
        $model->flushModelCache();

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
        return $this->dao->getRelation($model, $relation);
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
        return $this->dao->getRelationWhere($model, $relation, $constraints);
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
        $relatedModel   = $this->dao->storeRelation($model, $relationship, $data);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relatedModel->flushDaoCache();
        $relatedModel->flushModelCache();

        return $relatedModel;
    }

    /**
     * Update a relation of the model
     *
     * @param \EdStevo\Dao\Models\BaseModel   $model
     * @param string                                $relationship
     * @param \EdStevo\Dao\Models\BaseModel   $relation
     * @param array                                 $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function updateRelation(BaseModel $model, string $relationship, BaseModel $relation, array $data) : BaseModel
    {
        $result     = $this->dao->updateRelation($model, $relationship, $relation, $data);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relation->flushDaoCache();
        $relation->flushModelCache();

        return $result;
    }

    /**
     * Destroy a relation and fire an event attached with this model
     *
     * @param \EdStevo\Dao\Models\BaseModel   $model
     * @param string                                $relationship
     * @param \EdStevo\Dao\Models\BaseModel   $relation
     *
     * @return bool|null
     */
    public function destroyRelation(BaseModel $model, string $relationship, BaseModel $relation)
    {
        $result     = $this->dao->destroyRelation($model, $relationship, $relation);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relation->flushDaoCache();
        $relation->flushModelCache();

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
        $result     = $this->dao->attach($model, $relationship, $relation, $pivot_data);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relation->flushDaoCache();
        $relation->flushModelCache();

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
        $result     = $this->dao->sync($model, $relationship, $relation_id, $detaching);

        $model->flushDaoCache();
        $model->flushModelCache();

        return $result;
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
        $result     = $this->dao->updatePivot($model, $relationship, $relation, $pivot_data);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relation->flushDaoCache();
        $relation->flushModelCache();

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
        $result     = $this->dao->detach($model, $relationship, $relation);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relation->flushDaoCache();
        $relation->flushModelCache();

        return $result;
    }

    /**
     * Toggle a many to many relationship between these models
     *
     * @param \EdStevo\Dao\Models\BaseModel   $model
     * @param string                                $relationship
     * @param \EdStevo\Dao\Models\BaseModel   $relation
     *
     * @return array
     */
    public function toggle(BaseModel $model, string $relationship, BaseModel $relation) : array
    {
        $result     = $this->dao->toggle($model, $relationship, $relation);

        $model->flushDaoCache();
        $model->flushModelCache();
        $relation->flushDaoCache();
        $relation->flushModelCache();

        return $result;
    }

    /**
     * Get the validation rules associated with storing a model
     *
     * @return array
     */
    public function getStoreRules() : array
    {
        return $this->dao->getStoreRules();
    }

    /**
     * Get the validation rules associated with updating a model
     *
     * @return array
     */
    public function getUpdateRules() : array
    {
        return $this->dao->getUpdateRules();
    }

    /**
     * Get the name of the db table for this model
     *
     * @return string
     */
    public function getTable() : string
    {
        return $this->dao->getTable();
    }

    /**
     * Generate against this dao
     *
     * @return \EdStevo\Dao\Contracts\DaoGenerator
     */
    public function generate(bool $persist = true) : DaoGeneratorContract
    {
        return $this->dao->generate($persist);
    }

    /**
     * Validate against this dao
     *
     * @return \EdStevo\Dao\Contracts\DaoValidator
     */
    public function validate(array $rules = []) : DaoValidatorContract
    {
        return $this->dao->validate($rules);
    }

    /**
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function resetScope() : DaoBaseContract
    {
        $this->dao->resetScope();

        return $this;
    }

    /**
     * @param bool $status
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function skipCriteria(bool $status = true) : DaoBaseContract
    {
        return $this->dao->skipCriteria($status = true);
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->dao->getCriteria();
    }

    /**
     * @param \EdStevo\Dao\Repositories\DaoCriteria $criteria
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function getByCriteria(DaoCriteria $criteria) : DaoBaseContract
    {
        $this->dao->getByCriteria($criteria);

        return $this;
    }

    /**
     *
     *
     * @param \EdStevo\Dao\Repositories\DaoCriteria $criteria
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function pushCriteria(DaoCriteria $criteria) : DaoBaseContract
    {
        $this->dao->pushCriteria($criteria);

        return $this;
    }

    /**
     * Get governing tag name for this cache
     *
     * @return string
     */
    public function getBaseTag() : string
    {
        return $this->getCacheTagName();
    }

    /**
     * Return the main tags associated with this class
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCacheTags() : Collection
    {
        return $this->tags;
    }

    /**
     * Flush entire dao cache
     *
     * @param array $otherTags
     *
     * @return mixed
     */
    public function flushCache(array $otherTags = [])
    {
        return $this->cache->flush($this->getCacheTags()->merge($otherTags));
    }

    /**
     * Flush the cache store which lists all records for this model
     *
     * @param array $otherTags
     *
     * @return mixed
     */
    public function flushListCache() : DaoCacheContract
    {
        $this->cache->flush($this->listCacheTag);

        return $this;
    }

    /**
     * Flush the cache store which lists a single instance of this model
     *
     * @param array $otherTags
     *
     * @return mixed
     */
    public function flushSingeCache($id) : DaoCacheContract
    {
        $this->cache->flush($this->getSingleCacheTag($id));

        return $this;
    }

    /**
     * Get the Class Name from a namespace
     *
     * @param $namespace
     *
     * @return mixed
     */
    private function getCacheTagName()
    {
        return basename($this->dao->getTable());
    }

    /**
     * Return the cache key for a single record
     *
     * @param $id
     *
     * @return string
     */
    private function getSingleCacheTag($id) : string
    {
        return $this->getCacheTagName() . "_" . $id;
    }

    /**
     * Get this criteria tags
     *
     * @return string
     */
    private function criteriaTags() : string
    {
        return "_" . $this->dao->getCriteria()->map(function($class) {
                return $this->getCacheTagName();
            })->implode("_");
    }

    /**
     * Put a request through the cache
     *
     * @param array $tags
     * @param int   $minutes
     *
     * @return mixed
     */
    public function cacheRequest(array $tags = [], int $minutes = 60)
    {
        $previous   = debug_backtrace()[1];
        $tag        = $previous['function'] . "_" . implode("_", $previous['args']) . $this->criteriaTags();

        return $this->cache->tags($this->getCacheTags()->merge($tags)->toArray())->remember($tag, $minutes, function() use ($previous) {

            return call_user_func_array([
                $this->dao,
                $previous['function']
            ], $previous['args']);
        });
    }
}