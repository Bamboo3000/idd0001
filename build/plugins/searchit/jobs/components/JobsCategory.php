<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Searchit\Jobs\Models\Job;
use Searchit\Jobs\Models\Type;
use Searchit\Jobs\Models\Category;
use DB;
use Lang;
use Collection;

class JobsCategory extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Jobs Categories Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * @var object of jobs
     */
    protected $jobs;
    
    /**
     * @var array of categories without parent to display
     */
    protected $catsArr;
    
    /**
     * @var array of parameters from inputs
     */
    protected $parameters = [];

    /**
     * @var array of parameters used during filtering and for pagination after filtering
     */
    protected $params = [
        'title',
        'type',
        'location',
        'category',
        'salary-min',
        'salary-max',
        'gclid'
    ];

    public function defineProperties()
    {
        return [
            'categorySlug' => [
            'title' => 'Category Slug',
            'type' => 'string',
            'default' => '{{ :category }}',
            ],
        ];
    }

    public function onRun()
    {
        $this->jobs = new Job;

        $this->page['cats'] = $this->getCategories(Category::withCount('jobs')->with('jobs')->has('jobs', '>=', 1)->orderBy('jobs_count', 'asc')->get());
        $this->page['types'] = Type::get();

        $this->page['jobs'] = $this->loadResults()->orderBy('date', 'desc')->paginate(20);
        $this->page['jobsCount'] = $this->loadResults()->orderBy('date', 'desc')->count();
        $this->page['pagination'] = $this->page['jobs'];
    }

    protected function loadResults()
    {
        $category = $this->property('categorySlug');
        return Job::whereHas('categories', function($query) use ($category) {
            $query->where('category_slug', $category);
        });
    }

    /**
     *
     * Getting categories without parents
     *
     */
    protected function getCategories($array)
    {
      $this->catsArr = $array;
      foreach($array as $cat) {
        if($cat->parent != 0) {
          $category = $this->catsArr->where('id', $cat->parent)->first();
          if($category['children']) {
            $category['children'] = array_prepend($category['children'], $cat);
          } else {
            $category['children'] = [$cat]; 
          }
        }
      }
      return $this->catsArr->where('parent', 0)->sortBy('id')->all();
    }

    /**
     *
     * Querying jobs from DB
     *
     */
    protected function prepareJobs()
    {
      $this->jobs = new Job;

      $title = input('title');
      $type = input('type');
      $location = input('location');
      $category = input('category');
      $salaryMin = input('salary-min');
      $salaryMax = input('salary-max');
      $adwords = input('gclid'); // does completelly nothing in code, have to be here in order for adwords counter to work

      foreach ($this->params as $param) {
        if(!empty(input($param))) {
          $this->parameters[$param] = input($param);
        }
      }

      if(!empty($title)) {
        $this->jobs = $this->jobs->where(function($query) use ($title) {
            $query->where('title', 'like', "%{$title}%")
            ->orWhere('summary', 'like', "%{$title}%");
        });
      }
      if(!empty($location)) {
        $this->jobs = $this->jobs->where('location', 'like', "%{$location}%");
      }
      if(!empty($type)) {
        $this->jobs = $this->jobs->whereHas('types', function($query) use ($type) {
            $query->whereIn('id', $type);
        });
      }
      if(!empty($category)) {
        $this->jobs = $this->jobs->whereHas('categories', function($query) use ($category) {
          $query->whereIn('id', $category);
        });     
      } elseif(empty($category)) {
        $fulfilledCategory = Category::where('category_slug', 'fulfilled')->pluck('id');
        $this->jobs = $this->jobs->whereHas('categories', function($query) use ($fulfilledCategory) {
          $query->where('category_id', '!=', $fulfilledCategory);
        });  
      }
      if(!empty($salaryMin)) {
        $this->jobs = $this->jobs->where('salary_min', '>=', $salaryMin);
      }
      if(!empty($salaryMax)) {
        $this->jobs = $this->jobs->where('salary_max', '<=', $salaryMax);
      }

      if(Lang::getLocale() == 'en') {
        $this->page['jobCat'] = "/en/jobs/";
        $this->page['jobUrl'] = "/en/job/";
      } else {
        $this->page['jobCat'] = "/nl/vacatures/";
        $this->page['jobUrl'] = "/nl/vacature/";
      }
      $this->page['search'] = $title;
      $this->page['location'] = $location;
      $this->page['jobsCount'] = $this->jobs->count();
      $this->page['jobs'] = $this->jobs->orderBy('date', 'desc')->paginate(20);
      $this->page['pagination'] = $this->page['jobs']->appends($this->parameters);
    }

    /**
     *
     * Function to run when filtering jobs
     *
     */
    protected function onFilterSearch()
    {
      $this->prepareJobs();
    }

}