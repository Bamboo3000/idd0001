<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Lang;
use Redirect;

class LangCheck extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Language Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun() 
    {
        if(Lang::getLocale() == 'en') {
            $this->page['jobCat'] = "/en/jobs/";
            $this->page['jobUrl'] = "/en/job/";
        } else {
            $this->page['jobCat'] = "/nl/vacatures/";
            $this->page['jobUrl'] = "/nl/vacature/";
        }
    }

    public function onSearchRedirect()
    {
        $input = input('search-input');
        if(Lang::getLocale() == 'en') {
            $url = "/en/jobs?title=$input";
        } else {
            $url = "/nl/vacatures?title=$input";
        }
        return Redirect::to($url);
    }

}