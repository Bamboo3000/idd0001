<?php namespace Searchit\Jobs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitJobsTypes2 extends Migration
{
    public function up()
    {
        Schema::table('searchit_jobs_types', function($table)
        {
            $table->increments('id');
        });
    }
    
    public function down()
    {
        Schema::table('searchit_jobs_types', function($table)
        {
            $table->dropColumn('id');
        });
    }
}
