<?php

use WuriN7i\Balance\Services\VoucherGenerator;

beforeEach(function () {
    $this->generator = new VoucherGenerator;
});

test('validates correct voucher format', function () {
    expect($this->generator->isValidFormat('VCH-2025-00001'))->toBeTrue()
        ->and($this->generator->isValidFormat('ABC-2024-99999'))->toBeTrue()
        ->and($this->generator->isValidFormat('XYZ-2023-00100'))->toBeTrue();
});

test('rejects invalid voucher format', function () {
    expect($this->generator->isValidFormat('VCH-25-00001'))->toBeFalse() // year too short
        ->and($this->generator->isValidFormat('VCH-2025-001'))->toBeFalse() // sequence too short
        ->and($this->generator->isValidFormat('VCHH-2025-00001'))->toBeFalse() // prefix too long
        ->and($this->generator->isValidFormat('VCH-2025'))->toBeFalse() // missing sequence
        ->and($this->generator->isValidFormat('vch-2025-00001'))->toBeFalse(); // lowercase prefix
});

test('parses valid voucher number', function () {
    $parts = $this->generator->parse('VCH-2025-00123');

    expect($parts)->toBe([
        'prefix' => 'VCH',
        'year' => 2025,
        'sequence' => 123,
    ]);
});

test('returns empty array for invalid voucher', function () {
    $parts = $this->generator->parse('INVALID');

    expect($parts)->toBe([]);
});

test('can set custom prefix', function () {
    $this->generator->setPrefix('DIV');

    $reflection = new ReflectionClass($this->generator);
    $property = $reflection->getProperty('prefix');
    $property->setAccessible(true);

    expect($property->getValue($this->generator))->toBe('DIV');
});

test('converts prefix to uppercase', function () {
    $this->generator->setPrefix('div');

    $reflection = new ReflectionClass($this->generator);
    $property = $reflection->getProperty('prefix');
    $property->setAccessible(true);

    expect($property->getValue($this->generator))->toBe('DIV');
});
