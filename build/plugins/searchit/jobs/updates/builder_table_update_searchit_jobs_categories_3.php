<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitJobsCategories3 extends Migration
{
    public function up()
    {
        Schema::table('searchit_jobs_categories', function($table)
        {
            $table->integer('parent')->nullable(false)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('searchit_jobs_categories', function($table)
        {
            $table->integer('parent')->nullable()->default(null)->change();
        });
    }
}
