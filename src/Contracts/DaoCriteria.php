<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Contracts;

use EdStevo\Dao\Repositories\DaoCriteria as DaoCriteriaBase;

interface DaoCriteria
{

    /**
     * Reset the criteria in the Dao
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function resetScope() : DaoBase;

    /**
     * Set the Dao to ignore any criteria
     *
     * @param bool $status
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function skipCriteria(bool $status = true) : DaoBase;

    /**
     * Push Criteria into the dao upon which the query with pull from
     *
     * @param \EdStevo\Dao\Repositories\DaoCriteria $criteria
     *
     * @return \EdStevo\Dao\Contracts\DaoBase
     */
    public function pushCriteria(DaoCriteriaBase $criteria) : DaoBase;

}