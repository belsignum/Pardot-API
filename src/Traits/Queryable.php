<?php

/**
 * Trait to allow the querying of specific object results in a generic way
 * 
 * Implementing classes should have 2 array properties
 * 
 * - $queryCriteria
 * - $queryNavigation
 * 
 * @category   PardotApi
 * @package    PardotApi
 * @author     Andrew Mc Cormack <andy@cyber-duck.co.uk>
 * @copyright  Copyright (c) 2018, Andrew Mc Cormack
 * @license    https://github.com/Cyber-Duck/Pardot-API/license
 * @version    1.0.0
 * @link       https://github.com/Cyber-Duck/Pardot-API
 * @since      1.0.0
 */
trait Queryable
{   
    /**
     * Sends the request to retrieve the object results and returns them from the API
     * 
     * /api/{operator}/version/{version}/do/query?...
     * 
     * required: user_key, api_key
     * 
     * @param array $criteria
     * @return string|null
     */
    public function query(array $criteria):? string
    {
        return $this->setOperator('query')->setData($criteria)->query('result');
    }
}