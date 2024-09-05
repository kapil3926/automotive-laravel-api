<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePartsSellingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts_sellings', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('brand_id');
            $table->string('version_id');
            $table->string('cat_id');
            $table->string('subCat_id');
            $table->string('modelYear');
            $table->string('quantity');
            $table->string('approxRate');
            $table->string('conditionPart');
            $table->tinyInteger('urgentSell')->default(0);
            $table->longText('image');
            $table->string('is_removed', 5)->default(0);
            $table->string('is_blocked', 5)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts_sellings');
    }
}
