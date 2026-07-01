<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class PaymentMethodService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data) {
            return $this->persist(new PaymentMethod, $data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($paymentMethod, $data) {
            return $this->persist($paymentMethod, $data);
        });
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        DB::transaction(function () use ($paymentMethod) {
            $wasDefault = $paymentMethod->is_default;

            $paymentMethod->delete();

            if ($wasDefault) {
                $this->ensureDefault();
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persist(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $isDefault = (bool) ($data['is_default'] ?? false);
        $isActive = (bool) ($data['is_active'] ?? true);

        if ($isDefault) {
            $isActive = true;
        }

        $paymentMethod->fill([
            'name' => $data['name'],
            'is_default' => $isDefault,
            'is_active' => $isActive,
        ]);

        $paymentMethod->save();

        if ($isDefault) {
            PaymentMethod::query()
                ->whereKeyNot($paymentMethod->id)
                ->update(['is_default' => false]);
        } else {
            $this->ensureDefault($paymentMethod->is_active ? $paymentMethod->id : null);
        }

        return $paymentMethod->refresh();
    }

    private function ensureDefault(?int $fallbackId = null): void
    {
        if (PaymentMethod::query()->where('is_default', true)->exists()) {
            return;
        }

        $fallback = null;
        if ($fallbackId) {
            $fallback = PaymentMethod::query()
                ->whereKey($fallbackId)
                ->where('is_active', true)
                ->first();
        }

        $fallback = $fallback ?: PaymentMethod::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if ($fallback) {
            $fallback->forceFill(['is_default' => true])->save();
        }
    }
}
