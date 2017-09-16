<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Dao\Models;

use EdStevo\Dao\Contracts\Example as ExampleContract;
use EdStevo\Dao\Models\Example as ExampleModel;
use EdStevo\Dao\Repositories\DaoBase;

class Example extends DaoBase implements ExampleContract
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    protected function model()
    {
        return ExampleModel::class;
    }

}