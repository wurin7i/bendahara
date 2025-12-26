<?php

namespace WuriN7i\Balance\Contracts;

use WuriN7i\Balance\Models\Transaction;

/**
 * Interface for generating voucher numbers.
 *
 * This interface allows the application layer to provide custom voucher number
 * generation logic, such as including division prefixes or custom formats.
 */
interface VoucherGeneratorInterface
{
    /**
     * Generate a unique voucher number for a transaction.
     *
     * This is typically called when a transaction is approved.
     * The generated voucher number should be unique across the system.
     *
     * Default format might be: "VCH-2025-00001"
     * Division-aware format might be: "ACR-2025-00001" (for Acara division)
     *
     * @param  Transaction  $transaction  The transaction to generate voucher number for
     * @return string The generated voucher number
     */
    public function generate(Transaction $transaction): string;

    /**
     * Validate if a voucher number follows the correct format.
     *
     * @param  string  $voucherNo  The voucher number to validate
     * @return bool True if valid format
     */
    public function isValidFormat(string $voucherNo): bool;

    /**
     * Parse a voucher number to extract components (year, sequence, prefix, etc).
     *
     * @param  string  $voucherNo  The voucher number to parse
     * @return array Array with components like ['prefix' => 'VCH', 'year' => 2025, 'sequence' => 1]
     */
    public function parse(string $voucherNo): array;
}
