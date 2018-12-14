<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Searchit\Jobs\Models\Job;
use Searchit\Jobs\Models\Category;
use Searchit\Jobs\Models\Type;
use Illuminate\Support\Facades\DB;

class Cronjob extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Cronjob Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * @var array of job IDs
     */
    protected $job_ids = [];

    /*
    *
    * Runs when cron job is being activated
    *
    */
    public function onRun() 
    {
        $this->readFile();
    }

    /*
    *
    * Checking if job has category IDDINK
    *
    */
    protected function checkForIddink($job)
    {
        $jobCategory = $job->categories->category;
        $categoryArray = [];
        foreach($jobCategory as $category) {
            if($category['group'] == '#2 Skill Area') {
                $categoryArray[] = $category;
            }
        }
        if(in_array("IDDINK", $categoryArray)) {
            return true;
        } else {
            return false;
        }
    }

    /*
    *
    * Parsing XML and update, delete or add new job to database, alongside with proper categories and type
    *
    */
    protected function readFile() 
    {
        $xml = simplexml_load_file('http://external.srch20.com/searchit/xml/jobs') or die("Error: Cannot create object");
        $vacancies = $xml->vacancy;

        foreach($vacancies as $job) {

            if($this->checkForIddink($job)) { /* Check for IDDINK job category */

                array_push($this->job_ids, $job->id);
                $date = date("Y-m-d H:i:s", strtotime($job->publish_date));
                
                if(!empty($job->url_title)) {
                    $slug = $job->url_title;
                } else {
                    $slug = $this->slugify( $job->title.'-'.$job->id );
                }
                $salary_min = preg_replace("/\./", "", $job->salary_fixed);
                $salary_max = preg_replace("/\./", "", $job->salary_bonus);
                if($job->meta) {
                    $meta_title = $job->meta;
                } else {
                    $meta_title = null;
                }
                if($job->custom_apply_text) {
                    $meta_keywords = $job->custom_apply_text;
                } else {
                    $meta_keywords = null;
                }
                if($job->custom_callback_button) {
                    $meta_description = $job->custom_callback_button;
                } else {
                    $meta_description = null;
                }

                /*
                *
                * Run when job in XML is already in database and modification date is different than one in database.
                *
                */
                if($this->getJobCount('job_id', $job->id) !== 0) {
                    if($this->getJobVal('job_id', $job->id, 'date') !== $date) {

                        Job::where('job_id', $job->id)->update(
                            [
                                'title'         => $job->title,
                                'summary'       => $job->description,
                                'date'          => $date,
                                'salary_min'    => $salary_min,
                                'salary_max'    => $salary_max,
                                'location'      => $job->address,
                                'lat'           => $job->lat,
                                'lng'           => $job->lng,
                                'slug'          => $slug,
                                'meta_title'    => $meta_title,
                                'meta_keywords' => $meta_keywords,
                                'meta_description' => $meta_description
                            ]
                        );

                    }
                } else {

                    Job::insertGetId(
                        [
                            'job_id'        => $job->id,
                            'title'         => $job->title,
                            'summary'       => $job->description,
                            'date'          => $date,
                            'salary_min'    => $salary_min,
                            'salary_max'    => $salary_max,
                            'location'      => $job->address,
                            'lat'           => $job->lat,
                            'lng'           => $job->lng,
                            'slug'          => $slug,
                            'meta_title'    => $meta_title,
                            'meta_keywords' => $meta_keywords,
                            'meta_description' => $meta_description
                        ]
                    );
                    
                }

                $this->setJobsCategories($job);

            } /* /Check for IDDINK job category */

        }

        $this->getFulfilledJobs();
        $this->removeDuplicateEntries();

    }

    /*
    *
    * Add categories and types for a job
    *
    */
    protected function setJobsCategories($job)
    {
        $jobCategory = $job->categories->category;
        $jobSingleCatPivot = DB::table('searchit_jobs_job_categories');
        $jobSingleTypePivot = DB::table('searchit_jobs_job_types');
        $jobSingleID = Job::where('job_id', $job->id)->value('id');

        foreach($jobCategory as $category) {
            if($category['group'] == '#2 Skill Area' || $category['group'] == '#3 Skill IT') {

                if($category == 'Sales' || $category == 'Recruitment') {
                    $cat = 'Recruitment and Sales';
                } else {
                    $cat = $category;
                }

                $jobSingleCatID = Category::where('category_name', $cat)->value('id');
                if($jobSingleCatID != 0) {
                    $jobSingleCatCheck = DB::table('searchit_jobs_job_categories')
                        ->where('job_id', $jobSingleID)
                        ->where('category_id', $jobSingleCatID)
                        ->first();
                    if(!$jobSingleCatCheck) {
                        DB::table('searchit_jobs_job_categories')->insert([ 'job_id' => $jobSingleID, 'category_id' => $jobSingleCatID ]);
                    }
                }

            } else if($category['group'] == '#1 Availability') {
                $jobSingleTypeID = Type::where('type_name', $category)->value('id');
                if($jobSingleTypeID != 0) {
                    $jobSingleTypeCheck = DB::table('searchit_jobs_job_types')
                        ->where('job_id', $jobSingleID)
                        ->where('type_id', $jobSingleTypeID)
                        ->first();
                    if(!$jobSingleTypeCheck) {
                        $jobSingleTypePivot->insert(['job_id' => $jobSingleID, 'type_id' => $jobSingleTypeID ]);
                    }
                } else {
                    Type::insertGetId(
                        [
                            'type_name'     => $category,
                            'type_slug'     => $this->slugify($category)
                        ]
                    );
                    $jobSingleTypeID = Type::where('type_name', $category)->value('id');
                    $jobSingleTypePivot->insert(['job_id' => $jobSingleID, 'type_id' => $jobSingleTypeID ]);
                }
            }

        }
    }

    /*
    *
    * Check if job id from database is present in XML, if not, add "fulfilled" category to it.
    *
    */
    protected function getFulfilledJobs()
    {
        Job::orderBy('id', 'desc')->chunk(50, function($jobs) {
            foreach($jobs as $job) {

                $jobSingleCatID = Category::where('category_slug', 'fulfilled')->value('id');
                $isJobFulfilled = DB::table('searchit_jobs_job_categories')
                    ->where('job_id', $job->id)
                    ->where('category_id', $jobSingleCatID)
                    ->count();
                
                if(!in_array($job->job_id, $this->job_ids) && $isJobFulfilled === 0) {

                    DB::table('searchit_jobs_job_categories')
                        ->where('job_id', $job->id)
                        ->delete();

                    DB::table('searchit_jobs_job_types')
                        ->where('job_id', $job->id)
                        ->delete();

                    DB::table('searchit_jobs_job_categories')
                        ->insert([ 'job_id' => $job->id, 'category_id' => $jobSingleCatID ]);

                } elseif(!in_array($job->job_id, $this->job_ids) && $isJobFulfilled === 1) {

                    DB::table('searchit_jobs_job_types')
                        ->where('job_id', $job->id)
                        ->delete();

                } elseif(in_array($job->job_id, $this->job_ids) && $isJobFulfilled === 1) {

                    DB::table('searchit_jobs_job_categories')
                        ->where('job_id', $job->id)
                        ->where('category_id', $jobSingleCatID)
                        ->delete();
                    
                }
            }
        });
    }

    /*
    *
    * Remove duplicated pivot table entries for categories and types
    *
    */
    protected function removeDuplicateEntries()
    {
        Job::orderBy('id', 'desc')->chunk(50, function($jobs) {
            foreach($jobs as $job) {
                $jobCategories = DB::table('searchit_jobs_job_categories')
                ->where('job_id', $job->id)
                ->get();

                $repCatArr = [];
                foreach($jobCategories as $cats) {
                    array_push($repCatArr, $cats->category_id);
                }
                $repetitionCat = array_count_values($repCatArr);
                
                foreach($repetitionCat as $key => $value) {
                    if($value > 1) {
                        DB::table('searchit_jobs_job_categories')
                        ->where('job_id', $job->id)
                        ->where('category_id', $key)
                        ->delete();
                    }
                }

                $jobTypes = DB::table('searchit_jobs_job_types')
                    ->where('job_id', $job->id)
                    ->get();
                
                $repTypArr = [];
                foreach($jobTypes as $types) {
                    array_push($repTypArr, $types->type_id);
                }
                $repetitionType = array_count_values($repTypArr);
                
                foreach($repetitionType as $key => $value) {
                    if($value > 1) {
                        DB::table('searchit_jobs_job_types')
                        ->where('job_id', $job->id)
                        ->where('type_id', $key)
                        ->delete();
                    }
                }
            }
        });
        
    

    }

    /*
    *
    * Return the number of rows founded
    *
    */
    protected function getJobCount($key, $value)
    {
        return Job::where($key, $value)->count();
    }

    /*
    *
    * Return the value from a table row
    *
    */
    protected function getJobVal($key, $value, $pluck)
    {
        return Job::where($key, $value)->pluck($pluck);
    }

    /*
    *
    * Make valid slug from a string
    *
    */
    protected function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

}