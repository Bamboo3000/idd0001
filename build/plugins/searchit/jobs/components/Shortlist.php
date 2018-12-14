<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Input;
use Redirect;
use Cookie;

class Shortlist extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Shortlist Component',
            'description' => 'No description provided yet...'
        ];
    } 

    protected function getShortlist()
    {

    }

    protected function onAddShortlist()
    {
        
    }

}