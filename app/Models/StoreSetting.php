<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $table = 'store_settings';

    public $timestamps = false;

    protected $fillable = [
        'delivery_fee',
        'bank_accounts',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'ewallet_provider',
        'ewallet_account_number',
        'ewallet_account_name',
        'qris_image_path',
    ];

    protected $casts = [
        'delivery_fee' => 'integer',
        'bank_accounts' => 'array',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['delivery_fee' => 15000],
        );
    }

    public function paymentInstructions(string $paymentMethod): array
    {
        if ($paymentMethod === 'transfer') {
            $bankAccounts = $this->bankAccounts();
            $primaryAccount = $bankAccounts[0] ?? [];

            return [
                'bank_accounts' => $bankAccounts,
                'bank_name' => $primaryAccount['bank_name'] ?? null,
                'account_number' => $primaryAccount['account_number'] ?? null,
                'account_name' => $primaryAccount['account_name'] ?? null,
            ];
        }

        if ($paymentMethod === 'ewallet') {
            return [
                'provider' => $this->ewallet_provider,
                'account_number' => $this->ewallet_account_number,
                'account_name' => $this->ewallet_account_name,
                'qris_image_path' => $this->qris_image_path,
            ];
        }

        return [];
    }

    public function bankAccounts(): array
    {
        $accounts = collect($this->bank_accounts ?? [])
            ->filter(fn ($account) => is_array($account))
            ->map(fn (array $account) => [
                'bank_name' => trim((string) ($account['bank_name'] ?? '')),
                'account_number' => trim((string) ($account['account_number'] ?? '')),
                'account_name' => trim((string) ($account['account_name'] ?? '')),
            ])
            ->filter(fn (array $account) => filled($account['bank_name'])
                && filled($account['account_number'])
                && filled($account['account_name']))
            ->values()
            ->all();

        if ($accounts !== []) {
            return $accounts;
        }

        if (filled($this->bank_name) && filled($this->bank_account_number) && filled($this->bank_account_name)) {
            return [[
                'bank_name' => $this->bank_name,
                'account_number' => $this->bank_account_number,
                'account_name' => $this->bank_account_name,
            ]];
        }

        return [];
    }
}
