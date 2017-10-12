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
     * Custom Validation Rules
     *
     * @var array
     */
    protected $rules;

    public function __construct(DaoBase $dao)
    {
        $this->dao      = $dao;
        $this->rules    = [];
    }

    /**
     * Set other rules on this validator
     *
     * @param $rules
     *
     * @return \EdStevo\Dao\Repositories\DaoValidator
     */
    public function setRules($rules) : DaoValidator
    {
        $this->rules    = $rules;

        return $this;
    }

    /**
     * Validate the storing of a model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data = [])
    {
        return $this->validate($this->dao->getStoreRules(), $data);
    }

    /**
     * Validate the update of a model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function update(array $data = [])
    {
        return $this->validate($this->dao->getUpdateRules(), $data);
    }

    /**
     * Validate some data for against this model
     *
     * @param array $rules
     * @param array $data
     *
     * @return mixed
     */
    private function validate(array $rules, array $data = [])
    {
        if (empty($data))
        {
            $data   = request()->all();
        }

        $rules  = array_merge($rules, $this->rules);

        return Validator::make($data, $rules)->validate();
    }

}