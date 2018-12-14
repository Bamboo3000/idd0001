<?php namespace Searchit\Team\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitTeam extends Migration
{
    public function up()
    {
        Schema::create('searchit_team_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('position');
            $table->text('email');
            $table->text('phone');
            $table->text('social');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_team_');
    }
}
