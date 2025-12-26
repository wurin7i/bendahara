<?php

namespace WuriN7i\Balance\Services;

use WuriN7i\Balance\Contracts\VoucherGeneratorInterface;
use WuriN7i\Balance\Models\Transaction;

class VoucherGenerator implements VoucherGeneratorInterface
{
    /**
     * Default prefix for voucher numbers.
     */
    protected string $prefix = 'VCH';

    /**
     * Generate a unique voucher number for a transaction.
     *
     * Format: PREFIX-YEAR-SEQUENCE
     * Example: VCH-2025-00001
     */
    public function generate(Transaction $transaction): string
    {
        $year = $transaction->date->format('Y');
        $sequence = $this->getNextSequence($year);

        return sprintf('%s-%s-%05d', $this->prefix, $year, $sequence);
    }

    /**
     * Get the next sequence number for the given year.
     */
    protected function getNextSequence(string $year): int
    {
        $pattern = "{$this->prefix}-{$year}-%";

        $lastVoucher = Transaction::where('voucher_no', 'LIKE', $pattern)
            ->orderBy('voucher_no', 'desc')
            ->value('voucher_no');

        if (! $lastVoucher) {
            return 1;
        }

        $parts = $this->parse($lastVoucher);

        return ($parts['sequence'] ?? 0) + 1;
    }

    /**
     * Validate if a voucher number follows the correct format.
     */
    public function isValidFormat(string $voucherNo): bool
    {
        // Format: PREFIX-YEAR-SEQUENCE (e.g., VCH-2025-00001)
        $pattern = '/^[A-Z]{3}-\d{4}-\d{5}$/';

        return preg_match($pattern, $voucherNo) === 1;
    }

    /**
     * Parse a voucher number to extract components.
     */
    public function parse(string $voucherNo): array
    {
        if (! $this->isValidFormat($voucherNo)) {
            return [];
        }

        $parts = explode('-', $voucherNo);

        return [
            'prefix' => $parts[0],
            'year' => (int) $parts[1],
            'sequence' => (int) $parts[2],
        ];
    }

    /**
     * Set custom prefix for voucher numbers.
     *
     * This allows the application layer to customize prefixes
     * (e.g., division-specific prefixes in Bendahara).
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = strtoupper($prefix);

        return $this;
    }

    /**
     * Get current prefix.
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
