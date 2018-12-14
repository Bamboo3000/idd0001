<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitJobsJobCategories extends Migration
{
    public function up()
    {
        Schema::create('searchit_jobs_job_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('job_id');
            $table->integer('category_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_jobs_job_categories');
    }
}
