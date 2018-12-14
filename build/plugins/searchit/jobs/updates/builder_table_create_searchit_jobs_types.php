<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitJobsTypes extends Migration
{
    public function up()
    {
        Schema::create('searchit_jobs_types', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->string('type_name');
            $table->string('type_slug');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_jobs_types');
    }
}
