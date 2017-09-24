<?php namespace EdStevo\Dao;

/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class DaoServiceProvider extends ServiceProvider
{
    /**
     * Path To Dao Contracts
     *
     * @var string
     */
    protected $contractPath = 'Contracts/Dao/';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->getContracts()->each(function($daoModel) {

            $class  = $this->getContractNamespace($daoModel);

            app()->bind($class, function($app) use ($daoModel) {

                $daoRepository      = $this->getDaoNamespace($daoModel);
                $cacheRepository    = $this->getCacheNamespace($daoModel);

                return new $cacheRepository(resolve($daoRepository));
            });
        });
    }

    /**
     * Return all the Dao Contracts
     *
     * @return \Illuminate\Support\Collection
     */
    private function getContracts() : Collection
    {
        return collect(scandir(app_path($this->contractPath)))->map(function($contract) {
            return $this->getContractName($contract);
        });
    }

    /**
     * Return the name of the contract
     *
     * @param string $contract
     *
     * @return string
     */
    private function getContractName(string $contract)
    {
        return str_replace(".php", "", basename($contract));
    }

    /**
     * Return the Contract Namespace of a Dao Model
     *
     * @param $daoModel
     *
     * @return string
     */
    private function getContractNamespace($daoModel)
    {
        return 'App\\Contracts\\Dao\\' . $daoModel;
    }

    /**
     * Return the Cache Namespace of a Dao Model
     *
     * @param $daoModel
     *
     * @return string
     */
    private function getCacheNamespace($daoModel)
    {
        return 'App\\Dao\\Caches\\' . $daoModel;
    }

    /**
     * Return the Dao Namespace of a Dao Model
     *
     * @param $daoModel
     *
     * @return string
     */
    private function getDaoNamespace($daoModel)
    {
        return 'App\\Dao\\Models\\' . $daoModel;
    }
}