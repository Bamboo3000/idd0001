<?php namespace Searchit\Testimonials\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSearchitTestimonialsTestimonials extends Migration
{
    public function up()
    {
        Schema::create('searchit_testimonials_testimonials', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('summary');
            $table->string('who');
            $table->string('type');
            $table->boolean('featured')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('searchit_testimonials_testimonials');
    }
}
