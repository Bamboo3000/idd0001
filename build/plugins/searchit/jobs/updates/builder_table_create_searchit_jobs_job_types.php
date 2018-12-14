<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitJobsJobTypes extends Migration
{
    public function up()
    {
        Schema::create('searchit_jobs_job_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('job_id');
            $table->integer('type_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_jobs_job_types');
    }
}
