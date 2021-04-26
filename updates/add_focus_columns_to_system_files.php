<?php namespace OFFLINE\ResponsiveImages\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddFocusColumnsToSystemFiles extends Migration
{
    public function up()
    {
        Schema::table('system_files', function ($table) {
            $table->decimal('offline_responsiveimages_focus_x_axis', 5, 2)->nullable();
            $table->decimal('offline_responsiveimages_focus_y_axis', 5, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('system_files', function ($table) {
            $table->dropColumn([
                'offline_responsiveimages_focus_x_axis',
                'offline_responsiveimages_focus_y_axis'
            ]);
        });
    }
}
