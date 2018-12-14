<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Searchit\Jobs\Models\Job;
use Searchit\Jobs\Models\Category;

class JobFulfilled extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Job Fulfilled Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun()
    {
        $slug = 'fulfilled';
        $jobs = Job::whereHas('categories', function($query) use ($slug) {
            $query->where('category_slug', $slug);
        })->get();
        foreach($jobs as $job) {
            if($job->slug == $this->property('jobfulfilled')) {
                $this->page['fulfilled'] = true;
            }
        }
    }

}