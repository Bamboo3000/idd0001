<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Searchit\Jobs\Models\Job;

class RecentJobs extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Recent Jobs Component',
            'description' => 'No description provided yet...'
        ];
    }

    private $jobs;

    public function onRun()
    {

      $this->jobs = new Job;
      $this->page['recent'] = $this->jobs->orderBy('date', 'desc')->paginate(12);

    }

}
