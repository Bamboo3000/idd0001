<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Redirect;
use Flash;
use Input;
use Mail;

class FormClient extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Form Client Component',
            'description' => 'No description provided yet...'
        ];
    }

    /*
    *
    * Form submit script
    *
    */
    protected function onFormClientSubmit()
    {

        $inputs = [
            'name'      => Input::get('name'),
            'company'   => Input::get('company'),
            'phone'     => Input::get('phone'),
            'email'     => Input::get('email'),
            'extra'     => Input::get('extra'),
        ];  

        Mail::send('searchit.jobs::mail.message', $inputs, function($message){

            $message->from('searchit@recruitment.com', 'Searchit VPS');
            $message->to('info@searchitrecruitment.com', 'Admin Searchit');
            $message->subject('Searchit Recruitment new job spec');

            if(Input::file('job_file')) {
                $file_name = Input::file('job_file')->getClientOriginalName();
                $mime_type = Input::file('job_file')->getMimeType();
                $message->attach(Input::file('job_file'), ['as' => $file_name, 'mime' => $mime_type]);
            }

        });

        Flash::success('job');

        return Redirect::back();
        die();

    }

}