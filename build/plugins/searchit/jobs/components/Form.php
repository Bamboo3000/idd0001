<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Input;
use Flash;
use Redirect;
use Request;
use Lang;
use Mail;
use Validator;
use Searchit\Jobs\Models\Job;
use System\Models\File as FileSys;
use \Anhskohbo\NoCaptcha\NoCaptcha;

class Form extends ComponentBase
{

    protected $key = 'XoslTEyE';
    protected $secret = 'ZZXRgDovPQvPfLjklPLBoTAl';
    protected $endpoint = 'people/add_to_queue';

    public function componentDetails()
    {
        return [
            'name'        => 'Form Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun()
	{
        $this->page['cvCaptcha'] = app('captcha')->display(['data-callback' => 'cvCaptchaCallback']);
        $this->page['appCaptcha'] = app('captcha')->display(['data-callback' => 'appCaptchaCallback']);
	}

    /*
    *
    * Form submit script
    *
    */
    protected function onFormSubmit()
    {

        $rules = [
			'applicant-name'	    => 'required|min:3',
			'applicant-email'		=> 'required|email',
			'g-recaptcha-response'  => 'required|captcha'
        ];
        $validator = Validator::make(Input::all(), $rules);

        if($validator->fails()){
            $messages = $validator->messages();
            foreach ($messages->all() as $message) {
                Flash::error($message);
            }
		} else {

            if(env('APP_ENV') !== 'dev') {

                if(Input::hasFile('applicant-cv')) {

                    if(Input::file('applicant-cv')->getSize() < (2097152)) { //can't be larger than 2 MB
                        $file = new FileSys;
                        $file->data = Input::file('applicant-cv');
                        $file->save();
                    } else {
                        throw new ValidationException('Oops!  Your file\'s size is to large.');
                    }

                }
                
                if (!function_exists('curl_file_create')) {
                    function curl_file_create($filename, $mimetype = '', $postname = '') {
                        return "@$filename;filename="
                            . ($postname ?: basename($filename))
                            . ($mimetype ? ";type=$mimetype" : '');
                    }
                }

                $signature = bin2hex(hash_hmac('sha1', $this->endpoint.'/'.$this->key, $this->secret, true));
                $uri = "http://api.searchsoftware.nl/{$this->endpoint}?api_key={$this->key}&signature={$signature}";
                // Set up the url

                $application_data = array(
                    'name' => Input::get('applicant-name'),
                    'email' => Input::get('applicant-email'),
                    'gender' => Input::get('gender'),
                    'birthdate' => Input::get('dob'),
                    'location' => array(
                        'line1' => Input::get('applicant-street'),
                        'city' => Input::get('applicant-city'),
                        'zip' => Input::get('applicant-zip'), 
                        'country' => Input::get('applicant-country'),
                    ),
                    'phone' => Input::get('applicant-phone'),
                    'note' => array(
                        'text' => Input::get('applicant-message')
                    ),
                    'job' => array(
                        'id' => Input::get('job-id')
                    ),
                    'sources' => array(
                        array(
                            'parent_source_id' => Input::get('applicant-find'),
                            'name' => 'Applicant'
                        )
                    )
                );

                if($application_data['location']['country'] === NULL) {
                    unset($application_data['location']['country']);
                }

                //var_dump($application_data);

                if(Input::hasFile('applicant-cv')){
                    $uploaded_file = Input::file('applicant-cv')->getRealPath();
                    $file_ext = Input::file('applicant-cv')->getMimeType();
                    $file_name = Input::file('applicant-cv')->getClientOriginalName();
                    $file_cv = curl_file_create($uploaded_file, $file_ext, $file_name);
                    $data = array(
                        'json' => json_encode($application_data),
                        'cv' => $file_cv
                    );
                } else {
                    $data = array(
                        'json' => json_encode($application_data)
                    );
                }

                //initialise the curl request
                $request = curl_init();

                curl_setopt($request, CURLOPT_URL, $uri);
                curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($request, CURLOPT_POST, 1);
                curl_setopt($request, CURLOPT_POSTFIELDS, $data);

                $reply = curl_exec($request);
                // close the session
                curl_close($request);

                $form_data = array(
                    'name' => Input::get('applicant-name'),
                    'email' => Input::get('applicant-email'),
                    'location' => array(
                        'line1' => Input::get('applicant-street'),
                        'city' => Input::get('applicant-city'),
                        'zip' => Input::get('applicant-zip'), 
                        'country' => Input::get('applicant-country'),
                    ),
                    'phone' => Input::get('applicant-phone'),
                    'gender' => Input::get('gender'),
                    'birthdate' => Input::get('dob'),
                    'note' => array(
                        'text' => Input::get('applicant-message')
                    ),
                    'sources' => array(
                        array(
                            'parent_source_id' => Input::get('applicant-find'),
                            'name' => 'Applicant'
                        )
                    ),
                    'job_title' => Job::where('job_id', Input::get('job-id'))->value('title'),
                    'job_link' => Request::url()
                );

                if(Input::get('form_type') == 'application') {
                    
                    if(Lang::getLocale() == 'en') {
                        $this->sendMail($form_data, 'Thanks for applying for a job at Search It Recruitment', 'application_en');
                    } else {
                        $this->sendMail($form_data, 'Bedankt voor solliciteren bij Search It Recruitment', 'application_nl');
                    }
                    Flash::success('app');

                } else {
                    
                    if(Lang::getLocale() == 'en') {
                        $this->sendMail($form_data, 'Thanks for uploading your resume at Search It Recruitment', 'resume_en');
                    } else {
                        $this->sendMail($form_data, 'Bedankt voor het uploaden van jouw cv bij Search It Recruitment', 'resume_nl');
                    }
                    Flash::success('cv');

                }

            } else {

                if(Input::get('form_type') == 'application') {
                    Flash::success('app');
                } else {
                    Flash::success('cv');
                }

            }

        }
        
        return Redirect::back();
        die();
    }

    protected function sendMail($inputs, $subject, $template)
    {
        Mail::send('searchit.jobs::mail.'.$template, $inputs, function($message) use ($inputs, $subject){

            $message->from('info@searchitrecruitment.com', 'Search It Recruitment');
            $message->to($inputs['email'], $inputs['name']);
            $message->subject($subject);

        });
    }

}