<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitJobsCategories extends Migration
{
    public function up()
    {
        Schema::table('searchit_jobs_categories', function($table)
        {
            $table->integer('parent_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('searchit_jobs_categories', function($table)
        {
            $table->dropColumn('parent_id');
        });
    }
}
