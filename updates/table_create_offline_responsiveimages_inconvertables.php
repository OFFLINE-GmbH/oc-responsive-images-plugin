<?php namespace OFFLINE\ResponiveImages\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class TableCreateOfflineResponsiveimagesInconvertables extends Migration
{
    public function up()
    {
        Schema::create('offline_responsiveimages_inconvertables', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('filename');
            $table->string('path');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_gallery_galleries');
    }
}