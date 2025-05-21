<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('business_id');
            $table->string('code');
            $table->string('level');
            $table->string('name');
            $table->boolean('acative')->nullable()->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('cost_centers')->onDelete('cascade');
            $table->boolean('is_last_record')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_centers');
    }
};
