<?php namespace Searchit\Jobs\Components;

use Cms\Classes\ComponentBase;
use Input;
use Flash;
use Redirect;
use Request;
use Lang;
use Mail;
use Log;
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
                $uri = "https://api.searchsoftware.nl/{$this->endpoint}?api_key={$this->key}&signature={$signature}";
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
                Log::info('CURL logging for form submition: '.$reply);
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
                    
                    $this->sendMail($form_data, 'Bedankt voor solliciteren bij Iddink Group', 'application_nl');
                    Flash::success('app');

                }

            } else {

                Flash::success('app');

            }

        }
        
        return Redirect::back();
        die();
    }

    protected function sendMail($inputs, $subject, $template)
    {
        Mail::send('searchit.jobs::mail.'.$template, $inputs, function($message) use ($inputs, $subject){

            $message->from('info@werkenbijiddink.nl', 'Iddink Group');
            $message->to($inputs['email'], $inputs['name']);
            $message->subject($subject);

        });
    }

}