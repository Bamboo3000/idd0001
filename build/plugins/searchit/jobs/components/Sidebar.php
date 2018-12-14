<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Searchit\Jobs\Models\Job;

class Sidebar extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Sidebar Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun() 
    {
        // $this->cats = $this->getCategories();
        // $this->cats_no = $this->getCatNo();
        // $this->types = $this->getTypes();
        // $this->types_no = $this->getTypesNo();
    }
    public $cats;
    public $cats_no;
    public $types;
    public $types_no;

    protected function getCategories() 
    {
        $cats_final = [];
        $cats = Job::select('category')->distinct()->orderBy('category', 'asc')->get();
        $cats_pluck = $cats->pluck('category')->all();
        foreach($cats_pluck as $cat_pluck) {
            $cat_single = preg_replace('/all/', '', $cat_pluck[1]);
            $cat_single_final = $cat_single;
            array_push($cats_final, $cat_single_final);
        }
        return $cats_final;
    }

    protected function getCatNo()
    {
        $cats_no = [];
        $cats_array = $this->getCategories();
        foreach($cats_array as $cat) {
            $cat_no = $this->getCatCount($cat);
            array_push($cats_no, $cat_no);
        }
        return $cats_no;
    }

    protected function getTypes()
    {
        $types = Job::select('type')->distinct()->orderBy('type', 'asc')->get();
        $types_pluck = $types->pluck('type')->all();
        return $types_pluck;
    }

    protected function getTypesNo()
    {
        $types_no = [];
        $types_array = $this->getTypes();
        foreach($types_array as $type) {
            $type_no = $this->getTypeCount($type);
            array_push($types_no, $type_no);
        }
        return $types_no;
    }

    /*
    *
    * Return the number of rows founded
    *
    */
    protected function getCatCount($value)
    {
        return Job::where('category', $value)->count();
    }

    /*
    *
    * Return the number of rows founded
    *
    */
    protected function getTypeCount($value)
    {
        return Job::where('type', $value)->count();
    }

}