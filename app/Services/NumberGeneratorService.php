<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class NumberGeneratorService
{
    public static function next(string $type): string
    {
        return DB::transaction(function () use ($type) {
            $year = (int) date('Y');
            
            $sequence = Sequence::where('type', $type)->lockForUpdate()->first();
            
            $config = [
                'intake'      => ['prefix' => 'MEI-I'],
                'job'         => ['prefix' => 'MEI-R'],
                'quotation'   => ['prefix' => 'MEI-Q'],
                'sales_order' => ['prefix' => 'MEI-INV'],
                'demo'        => ['prefix' => 'MEI-D'],
                'gate_pass'   => ['prefix' => 'MEI-G'],
            ];

            $typeConfig = $config[$type] ?? ['prefix' => 'MEI-X'];
            
            if (!$sequence) {
                $sequence = Sequence::create([
                    'type' => $type,
                    'prefix' => $typeConfig['prefix'],
                    'year' => $year,
                    'last_number' => 0,
                ]);
            } else {
                $sequence->prefix = $typeConfig['prefix'];
            }

            $sequence->last_number += 1;
            $sequence->save();

            // All types now use 8-digit padding
            return sprintf('%s-%08d', $sequence->prefix, $sequence->last_number);
        });
    }
}
