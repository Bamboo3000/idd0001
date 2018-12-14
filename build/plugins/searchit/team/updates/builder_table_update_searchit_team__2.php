<?php namespace Searchit\Team\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSearchitTeam2 extends Migration
{
    public function up()
    {
        Schema::table('searchit_team_', function($table)
        {
            $table->text('social_twitter');
            $table->renameColumn('social', 'social_linked');
        });
    }
    
    public function down()
    {
        Schema::table('searchit_team_', function($table)
        {
            $table->dropColumn('social_twitter');
            $table->renameColumn('social_linked', 'social');
        });
    }
}
