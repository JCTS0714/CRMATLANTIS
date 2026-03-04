<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('contador_customer')
            ->orderByDesc('id')
            ->get(['id', 'contador_id', 'customer_id']);

        $seenContadores = [];
        $seenCustomers = [];
        $deleteIds = [];

        foreach ($rows as $row) {
            $contadorId = (int) $row->contador_id;
            $customerId = (int) $row->customer_id;

            if (isset($seenContadores[$contadorId]) || isset($seenCustomers[$customerId])) {
                $deleteIds[] = (int) $row->id;
                continue;
            }

            $seenContadores[$contadorId] = true;
            $seenCustomers[$customerId] = true;
        }

        if (!empty($deleteIds)) {
            DB::table('contador_customer')->whereIn('id', $deleteIds)->delete();
        }

        $this->dropIndexIfExists('contador_customer', 'contador_customer_contador_id_customer_id_unique');
        if (!$this->indexExists('contador_customer', 'contador_customer_contador_id_unique')) {
            Schema::table('contador_customer', function (Blueprint $table) {
                $table->unique('contador_id');
            });
        }

        if (!$this->indexExists('contador_customer', 'contador_customer_customer_id_unique')) {
            Schema::table('contador_customer', function (Blueprint $table) {
                $table->unique('customer_id');
            });
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('contador_customer', 'contador_customer_customer_id_unique');
        $this->dropIndexIfExists('contador_customer', 'contador_customer_contador_id_unique');

        if (!$this->indexExists('contador_customer', 'contador_customer_contador_id_customer_id_unique')) {
            Schema::table('contador_customer', function (Blueprint $table) {
                $table->unique(['contador_id', 'customer_id']);
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(1) AS total FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        );

        return ((int) ($result->total ?? 0)) > 0;
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $indexName));
    }
};
