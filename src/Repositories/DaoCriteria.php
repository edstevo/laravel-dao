<?php
/**
 *  Copyright (c) 2016.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Repositories;

use EdStevo\Dao\Repositories\DaoBase;

abstract class DaoCriteria
{

    /**
     * @param   $model
     * @param   \EdStevo\Dao\Repositories\DaoBase   $repository
     * @return  mixed
     */
    public abstract function apply($model, DaoBase $repository);

}