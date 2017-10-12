<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Repositories;

use EdStevo\Dao\Contracts\DaoValidator as DaoValidatorContract;
use Illuminate\Support\Facades\Validator;

class DaoValidator implements DaoValidatorContract
{

    /**
     * Dao Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoBase
     */
    protected $dao;

    /**
     * Custom Validation Data
     *
     * @var array
     */
    protected $data;

    public function __construct(DaoBase $dao)
    {
        $this->dao      = $dao;
        $this->data     = [];
    }

    /**
     * Set other rules on this validator
     *
     * @param $rules
     *
     * @return \EdStevo\Dao\Repositories\DaoValidator
     */
    public function setData($data) : DaoValidator
    {
        if (empty($data))
        {
            $this->data     = request()->all();
        } else {
            $this->data     = $data;
        }

        return $this;
    }

    /**
     * Validate the storing of a model
     *
     * @param array $rules
     *
     * @return mixed
     */
    public function store(array $rules = [])
    {
        return $this->validate(array_merge($this->dao->getStoreRules(), $rules));
    }

    /**
     * Validate the update of a model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function update(array $rules = [])
    {
        return $this->validate(array_merge($this->dao->getUpdateRules(), $rules));
    }

    /**
     * Validate some data for against this model
     *
     * @param array $rules
     * @param array $data
     *
     * @return mixed
     */
    private function validate(array $rules)
    {
        return Validator::make($this->data, $rules)->validate();
    }

}