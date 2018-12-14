<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitJobsCategories2 extends Migration
{
    public function up()
    {
        Schema::table('searchit_jobs_categories', function($table)
        {
            $table->renameColumn('parent_id', 'parent');
        });
    }
    
    public function down()
    {
        Schema::table('searchit_jobs_categories', function($table)
        {
            $table->renameColumn('parent', 'parent_id');
        });
    }
}
