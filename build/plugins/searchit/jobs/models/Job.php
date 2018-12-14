<?php namespace Searchit\Jobs\Models;

use Model;

/**
 * Model
 */
class Job extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'searchit_jobs_job';

    // protected $jsonable = ['category'];

    public $belongsToMany = [

        'categories' => [
            'Searchit\Jobs\Models\Category',
            'table' => 'searchit_jobs_job_categories',
            'order' => 'category_name'
        ],

        'types' => [
            'Searchit\Jobs\Models\Type',
            'table' => 'searchit_jobs_job_types',
            'order' => 'type_name'
        ]

    ];
}