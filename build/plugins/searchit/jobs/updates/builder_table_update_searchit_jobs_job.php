<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitJobsJob extends Migration
{
    public function up()
    {
        Schema::rename('searchit_jobs_jobs', 'searchit_jobs_job');
    }
    
    public function down()
    {
        Schema::rename('searchit_jobs_job', 'searchit_jobs_jobs');
    }
}
