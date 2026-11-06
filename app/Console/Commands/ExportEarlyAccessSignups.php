<?php

namespace App\Console\Commands;

use App\Models\EarlyAccessSignup;
use Illuminate\Console\Command;

class ExportEarlyAccessSignups extends Command
{
    protected $signature = 'signups:export {path=storage/app/early-access-signups.csv : Where to write the CSV}';

    protected $description = 'Export early-access signups to a CSV file';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));
        @mkdir(dirname($path), 0755, true);
        $file = fopen($path, 'w');
        fputcsv($file, ['email', 'name', 'interest', 'source', 'signed_up_at', 'invited_at']);

        $count = 0;
        EarlyAccessSignup::orderBy('id')->each(function (EarlyAccessSignup $s) use ($file, &$count) {
            // Prefix cells that spreadsheets could read as formulas.
            $safe = fn (?string $v) => $v !== null && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v;
            fputcsv($file, [$s->email, $safe($s->name), $s->interest, $s->source, $s->consented_at, $s->invited_at]);
            $count++;
        });
        fclose($file);

        $this->info("Exported {$count} signups to {$path}");

        return self::SUCCESS;
    }
}
