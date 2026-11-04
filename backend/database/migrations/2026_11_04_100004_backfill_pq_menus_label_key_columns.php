<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_menus')) {
            return;
        }

        $codeColumn = Schema::hasColumn('pq_menus', 'procedimiento') ? 'procedimiento' : 'codigo';

        if (Schema::hasColumn('pq_menus', 'label_key')) {
            $rows = DB::table('pq_menus')
                ->select(['id', $codeColumn])
                ->where(function ($builder) {
                    $builder->whereNull('label_key')->orWhere('label_key', '');
                })
                ->get();

            foreach ($rows as $row) {
                $code = (string) ($row->{$codeColumn} ?? 'node_'.$row->id);
                DB::table('pq_menus')
                    ->where('id', $row->id)
                    ->update(['label_key' => 'menu.'.$code]);
            }
        }

        if (! Schema::hasColumn('pq_menus', 'labelKey')) {
            return;
        }

        $legacyRows = DB::table('pq_menus')
            ->select(['id', 'label_key', 'labelKey', $codeColumn])
            ->where(function ($builder) {
                $builder->whereNull('labelKey')->orWhere('labelKey', '');
            })
            ->get();

        foreach ($legacyRows as $row) {
            $labelKey = $row->label_key;
            if ($labelKey === null || $labelKey === '') {
                $code = (string) ($row->{$codeColumn} ?? 'node_'.$row->id);
                $labelKey = 'menu.'.$code;
            }

            DB::table('pq_menus')
                ->where('id', $row->id)
                ->update(['labelKey' => $labelKey]);
        }
    }

    public function down(): void
    {
        // Datos: no revertir.
    }
};
