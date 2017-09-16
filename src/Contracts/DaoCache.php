<?php
/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

namespace EdStevo\Dao\Contracts;

use Illuminate\Support\Collection;
use EdStevo\Dao\Contracts\DaoCache as DaoCacheContract;

interface DaoCache
{

    /**
     * Get governing tag name for this cache
     *
     * @return string
     */
    public function getBaseTag() : string;

    /**
     * Return the main tags associated with this class
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCacheTags() : Collection;

    /**
     * Flush this dao cache
     *
     * @param array $otherTags
     *
     * @return mixed
     */
    public function flushCache(array $otherTags = []);

    /**
     * Flush the cache store which lists all records for this model
     *
     * @param array $otherTags
     *
     * @return mixed
     */
    public function flushListCache() : DaoCacheContract;

    /**
     * Flush the cache store which lists a single instance of this model
     *
     * @param array $otherTags
     *
     * @return mixed
     */
    public function flushSingeCache($id) : DaoCacheContract;

}