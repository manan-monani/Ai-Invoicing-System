<?php

namespace App\Models;

class BusinessSetting extends BaseModel
{
    public const TAX_ENABLED = 'tax_enabled';

    public const TAX_MODE = 'tax_mode';

    public const DEFAULT_TAX_ID = 'default_tax_id';

    public const INVOICE_PREFIX = 'invoice_prefix';

    public const CURRENCY_POSITION = 'currency_position';

    public const INVOICE_TERMS = 'invoice_terms';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];
    //
}
