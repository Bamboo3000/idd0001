<?php namespace Searchit\Breadcrumbs\Components;

use Cms\Classes\ComponentBase;
use Redirect;

class HomeRedirect extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Homeredirect Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun() 
    {
        return Redirect::to('/');
    }

}