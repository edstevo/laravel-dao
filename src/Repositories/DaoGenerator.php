<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Repositories;

use EdStevo\Dao\Contracts\DaoGenerator as DaoGeneratorContract;
use Illuminate\Support\Collection;
use EdStevo\Dao\Models\BaseModel;

class DaoGenerator implements DaoGeneratorContract
{

    /**
     * Dao Base Repository
     *
     * @var \EdStevo\Dao\Repositories\DaoBase
     */
    protected $dao;

    /**
     * Return the namespace of the dao's model
     *
     * @var string
     */
    protected $modelNamespace;

    /**
     * Should generated models be stored in the DB?
     *
     * @var bool
     */
    protected $persist;

    public function __construct(DaoBase $dao)
    {
        $this->dao              = $dao;
        $this->modelNamespace   = get_class($this->dao->getModel());
        $this->setPersist();
    }

    /**
     * Set the persist value which dictates if generated models are stored or not.
     *
     * @param bool $value
     *
     * @return \EdStevo\Dao\Contracts\DaoGenerator
     */
    public function setPersist(bool $value = true) : DaoGeneratorContract
    {
        $this->persist  = $value;

        return $this;
    }

    /**
     * Generate examples of this model
     *
     * @param array $data
     *
     * @return \EdStevo\Dao\Models\BaseModel
     */
    public function single(array $data = []) : BaseModel
    {
        if ($this->persist)
        {
            return factory($this->modelNamespace)->create($data);
        }

        return factory($this->modelNamespace)->make($data);
    }

    /**
     * Generate multiple examples of this model
     *
     * @param int   $number
     * @param array $data
     *
     * @return \Illuminate\Support\Collection
     */
    public function multiple(int $number = 2, array $data = []) : Collection
    {
        if ($this->persist)
        {
            return factory($this->modelNamespace, $number)->create($data);
        }

        return factory($this->modelNamespace, $number)->make($data);
    }

}