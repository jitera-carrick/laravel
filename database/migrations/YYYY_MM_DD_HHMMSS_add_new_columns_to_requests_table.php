
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            $table->text('service_details');
            $table->date('preferred_date');
            $table->time('preferred_time');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->after('preferred_time');
        });
    }

    public function down()
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            $table->dropColumn(['service_details', 'preferred_date', 'preferred_time', 'status']);
        });
    }
}
