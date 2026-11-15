<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

class DocumentSequenceService
{
    public static function generate(string $sequenceKey): string
    {
        return Database::transaction(function (PDO $pdo) use ($sequenceKey) {
            $stmt = $pdo->prepare("
                SELECT * FROM document_sequences WHERE sequence_key = :key
            ");
            $stmt->execute([':key' => $sequenceKey]);
            $seq = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$seq) {
                // Default fallback if not pre-seeded
                $prefix = strtoupper(substr($sequenceKey, 0, 4));
                $pdo->prepare("
                    INSERT INTO document_sequences (sequence_key, prefix, current_number, padding, format_pattern)
                    VALUES (:key, :prefix, 1, 4, '{PREFIX}-{YEAR}{MONTH}-{NUMBER}')
                ")->execute([':key' => $sequenceKey, ':prefix' => $prefix]);

                $nextNumber = 1;
                $pattern = '{PREFIX}-{YEAR}{MONTH}-{NUMBER}';
                $padding = 4;
            } else {
                $nextNumber = (int)$seq['current_number'] + 1;
                $pdo->prepare("
                    UPDATE document_sequences
                    SET current_number = :num, updated_at = datetime('now', 'localtime')
                    WHERE sequence_key = :key
                ")->execute([':num' => $nextNumber, ':key' => $sequenceKey]);

                $prefix = $seq['prefix'];
                $pattern = $seq['format_pattern'];
                $padding = (int)$seq['padding'];
            }

            $year = date('Y');
            $month = date('m');
            $paddedNumber = str_pad((string)$nextNumber, $padding, '0', STR_PAD_LEFT);

            $formatted = str_replace(
                ['{PREFIX}', '{YEAR}', '{MONTH}', '{NUMBER}'],
                [$prefix, $year, $month, $paddedNumber],
                $pattern
            );

            return $formatted;
        });
    }
}
