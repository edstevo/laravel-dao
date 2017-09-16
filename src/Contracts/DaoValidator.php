<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Contracts;


interface DaoValidator
{

    /**
     * Validate the storing of a model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function store(array $data = []);

    /**
     * Validate the update of a model
     *
     * @param array $data
     *
     * @return mixed
     */
    public function update(array $data = []);

}