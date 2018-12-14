<?php namespace Searchit\Jobs;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Searchit\Jobs\Components\Cronjob' => 'cronjob',
    		'Searchit\Jobs\Components\Filters' => 'jobFilters',
            'Searchit\Jobs\Components\JobsList' => 'jobsList',
            'Searchit\Jobs\Components\RecentJobs' => 'recentJobs',
            'Searchit\Jobs\Components\JobsCategory' => 'jobsCategory',
            'Searchit\Jobs\Components\Sidebar' => 'sidebar',
            'Searchit\Jobs\Components\Form' => 'form',
            'Searchit\Jobs\Components\FormClient' => 'formClient',
            'Searchit\Jobs\Components\LangCheck' => 'langCheck',
            'Searchit\Jobs\Components\Shortlist' => 'shortlist',
            'Searchit\Jobs\Components\JobFulfilled' => 'jobFulfilled'
        ];
    }

    public function registerSettings()
    {
    }
}
