<?php

/**
 * @file plugins/generic/medgemmaParser/classes/MedgemmaSchemaMigration.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class MedgemmaSchemaMigration
 *
 * @brief Creates the table that stores one analysis per submission.
 */

namespace APP\plugins\generic\medgemmaParser\classes;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MedgemmaSchemaMigration extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medgemma_analyses')) {
            return;
        }
        Schema::create('medgemma_analyses', function (Blueprint $table) {
            $table->bigIncrements('analysis_id');
            $table->bigInteger('submission_id');
            $table->foreign('submission_id', 'medgemma_analyses_submission_id')
                ->references('submission_id')->on('submissions')->onDelete('cascade');
            $table->unique(['submission_id'], 'medgemma_analyses_submission_unique');

            $table->bigInteger('submission_file_id')->nullable();
            $table->string('status', 16);
            $table->longText('result')->nullable();
            $table->text('error')->nullable();
            $table->string('endpoint_name', 255)->nullable();
            $table->boolean('truncated')->default(false);
            $table->dateTime('date_created');
            $table->dateTime('date_modified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medgemma_analyses');
    }
}
