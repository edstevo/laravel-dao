<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Contracts;

use EdStevo\Dao\Models\BaseModel;
use Illuminate\Support\Collection;

interface DaoGenerator
{

    /**
     * Set the persist value which dictates if generated models are stored or not.
     *
     * @param bool $value
     *
     * @return \EdStevo\Dao\Repositories\DaoGenerator
     */
    public function setPersist(bool $value = true) : DaoGenerator;

    /**
     * Generate examples of this model
     *
     * @param array $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function single(array $data = []) : BaseModel;

    /**
     * Generate multiple examples of this model
     *
     * @param int   $number
     * @param array $data
     *
     * @return \Illuminate\Support\Collection
     */
    public function multiple(int $number = 2, array $data = []) : Collection;

}