<?php namespace Searchit\Team\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitTeam extends Migration
{
    public function up()
    {
        Schema::table('searchit_team_', function($table)
        {
            $table->text('photo');
        });
    }
    
    public function down()
    {
        Schema::table('searchit_team_', function($table)
        {
            $table->dropColumn('photo');
        });
    }
}
