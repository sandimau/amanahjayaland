<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page', 20);
            $table->string('selector', 1024);
            $table->string('kind', 20);
            $table->unsignedSmallInteger('node_index')->default(0);
            $table->string('label', 150)->nullable();
            $table->text('value');
            $table->timestamps();
            $table->index(['page', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
