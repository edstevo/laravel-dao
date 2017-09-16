<?php namespace EdStevo\Dao;

/**
 *  Copyright (c) 2017.
 *  This was created by Ed Stephenson (edward@flowflex.com).
 *  You must get permission to use this work.
 */

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class DaoServiceProvider
{
    protected $contractPath = 'Contracts/Dao/';
    protected $daoCachePath = 'Dao/Caches';
    protected $daoModelPath = 'Dao/Models';

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
        $this->getContracts()->each(function($contract) {

            app()->bind('App\\Contracts\\Dao\\' . $contract, function($app) use ($contract) {

                $daoRepository      = resolve('App\\Dao\\Models\\' . $contract);
                $cacheRepository    = resolve('App\\Dao\\Caches\\' . $contract);

                return new $cacheRepository($daoRepository);
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
        return collect(Storage::disk('app')->allFiles($this->contractPath))->map(function($contract) {
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
}