<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitJobsCategories extends Migration
{
    public function up()
    {
        Schema::create('searchit_jobs_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('category_name');
            $table->string('category_slug');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_jobs_categories');
    }
}
