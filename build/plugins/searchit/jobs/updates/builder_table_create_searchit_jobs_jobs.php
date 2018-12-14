<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitJobsJobs extends Migration
{
    public function up()
    {
        Schema::create('searchit_jobs_jobs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->text('summary');
            $table->dateTime('date');
            $table->integer('salary_min');
            $table->integer('salary_max');
            $table->string('location');
            $table->string('lat');
            $table->string('lng');
            $table->string('job_id');
            $table->string('slug');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_jobs_jobs');
    }
}
