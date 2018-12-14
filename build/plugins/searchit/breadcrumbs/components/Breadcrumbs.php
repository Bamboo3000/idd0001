<?php namespace Searchit\Breadcrumbs\Components;

use Cms\Classes\ComponentBase;
use Searchit\Jobs\Models\Job;
use Searchit\Jobs\Models\Category;
use Redirect;
use Request;
use URL;

class Breadcrumbs extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Breadcrumbs Component',
            'description' => 'No description provided yet...'
        ];
    }

    protected $segments = [];
    protected $catParent = 0;
    protected $catFirst = null;
    protected $catSecond = null;
    protected $jobName;

    protected function recursiveSegments($number)
    {
        if($number > 2) {
            $finalPath = $this->recursiveSegments($number-1) . '/' . Request::segment($number);
        } else {
            $finalPath = Request::segment($number);
        }
        return $finalPath;
    }

    protected function makeBreadcrumbs() 
    {
        $path = URL::to('/') . '/' . Request::segment(1);

        if($this->property('categorySlug')) {
            $cat = Category::where('category_slug', $this->property('categorySlug'))->first();
            if($cat && $cat->parent != 0){
                $this->catParent = $cat->parent;
                $this->catSecond = Category::where('category_slug', $this->property('categorySlug'))->first();
                $this->catFirst = Category::where('id', $this->catSecond->parent)->first();
            } else {
                $this->catFirst = Category::where('category_slug', $this->property('categorySlug'))->first();
            }
        }

        if($this->property('jobSlug')) {
            $slug = $this->property('jobSlug');
            // First level category object
            $this->catFirst = Category::whereHas('jobs', function($query) use ($slug) {
                $query->where('slug', $slug);
            })->where('parent', 0)->first();
            // Second level category object
            if($this->catFirst) {
                $this->catSecond = Category::whereHas('jobs', function($query) use ($slug) {
                    $query->where('slug', $slug);
                })->where('parent', $this->catFirst->id)->first();
            }
            // Job title
            if(Job::where('slug', $slug)->first()) {
                $this->jobName = Job::where('slug', $slug)->first()->title;
            } else {
                return Redirect::to('/jobs');
                exit;
            }
            
        }

        for($i = 2; $i <= count(Request::segments()); $i++) {
            $finalPath = $this->recursiveSegments($i);
            if($this->property('jobSlug')) {
                if(Request::segment($i) == 'job') {
                    $this->segments[] = [
                        'name' => 'Jobs',
                        'path' => $path . '/jobs'
                    ];
                    if($this->catFirst) {
                        $this->segments[] = [
                            'name' => $this->catFirst->category_name,
                            'path' => $path . '/jobs/' . $this->catFirst->category_slug
                        ];
                    }
                    if($this->catSecond !== null) {
                        $this->segments[] = [
                            'name' => $this->catSecond->category_name,
                            'path' => $path . '/jobs/' . $this->catSecond->category_slug
                        ];
                    }
                    $this->segments[] = [
                        'name' => $this->jobName
                    ];
                    break;
                } elseif(Request::segment($i) == 'vacature') {
                    $this->segments[] = [
                        'name' => 'Vacatures',
                        'path' => $path . '/vacatures'
                    ];
                    if($this->catFirst) {
                        $this->segments[] = [
                            'name' => $this->catFirst->category_name,
                            'path' => $path . '/vacatures/' . $this->catFirst->category_slug
                        ];
                    }
                    if($this->catSecond !== null) {
                        $this->segments[] = [
                            'name' => $this->catSecond->category_name,
                            'path' => $path . '/vacatures/' . $this->catSecond->category_slug
                        ];
                    }
                    $this->segments[] = [
                        'name' => $this->jobName
                    ];
                    break;
                }
            } elseif($this->property('categorySlug')) {
                if(Request::segment($i) == 'jobs') {
                    $this->segments[] = [
                        'name' => 'Jobs',
                        'path' => $path . '/jobs'
                    ];
                    if($this->catParent != 0) {
                        $this->segments[] = [
                            'name' => $this->catFirst->category_name,
                            'path' => $path . '/jobs/' . $this->catFirst->category_slug
                        ];
                        $this->segments[] = [
                            'name' => $this->catSecond->category_name
                        ];
                    } else {
                        $this->segments[] = [
                            'name' => $this->catFirst->category_name
                        ];
                    }
                    break;
                } elseif(Request::segment($i) == 'vacatures') {
                    $this->segments[] = [
                        'name' => 'Vacatures',
                        'path' => $path . '/vacatures'
                    ];
                    if($this->catParent != 0) {
                        $this->segments[] = [
                            'name' => $this->catFirst->category_name,
                            'path' => $path . '/vacatures/' . $this->catFirst->category_slug
                        ];
                        $this->segments[] = [
                            'name' => $this->catSecond->category_name
                        ];
                    } else {
                        $this->segments[] = [
                            'name' => $this->catFirst->category_name
                        ];
                    }
                    break;
                }
            } else {
                $this->segments[] = [
                    'name' => title_case(str_replace("-", " ", Request::segment($i))),
                    'path' => $path . '/' . $finalPath
                ];
            }
        }

        $this->page['segments'] = $this->segments;
        $this->page['baseUrl'] = $path;
    } 

    public function onRun() 
    {
        $this->makeBreadcrumbs();
    }

}