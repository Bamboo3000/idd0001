<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitJobsTypes extends Migration
{
    public function up()
    {
        Schema::table('searchit_jobs_types', function($table)
        {
            $table->dropColumn('id');
        });
    }
    
    public function down()
    {
        Schema::table('searchit_jobs_types', function($table)
        {
            $table->integer('id');
        });
    }
}
