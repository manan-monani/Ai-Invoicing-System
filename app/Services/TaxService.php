<?php

namespace App\Services;

use App\Models\Tax;

class TaxService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tax
    {
        return Tax::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tax $tax, array $data): Tax
    {
        $tax->update($data);

        return $tax;
    }

    public function delete(Tax $tax): void
    {
        $tax->delete();
    }
}
