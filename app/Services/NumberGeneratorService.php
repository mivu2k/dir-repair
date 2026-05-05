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
            
            if (!$sequence) {
                $prefixes = [
                    'intake' => 'mei-r',
                    'job' => 'mei-r',
                    'quotation' => 'mei-r',
                    'sales_order' => 'mei-r'
                ];
                $prefix = $prefixes[$type] ?? 'mei-r';
                
                $sequence = Sequence::create([
                    'type' => $type,
                    'prefix' => $prefix,
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            $sequence->last_number += 1;
            $sequence->save();

            // Format: PREFIX-XXXXXX (e.g., MEI-R-000001)
            return sprintf('%s-%06d', $sequence->prefix, $sequence->last_number);
        });
    }
}
