<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTaxSettingsRequest;
use App\Models\BusinessSetting;
use App\Models\Tax;
use App\Services\BusinessSettingService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TaxSettingsController extends Controller
{
    public function __construct(protected BusinessSettingService $businessSettingService) {}

    public function index(): \Inertia\Response
    {
        Gate::authorize(Permissions::TAX_SETUP);

        $settings = $this->businessSettingService->getSettings([
            BusinessSetting::TAX_ENABLED,
            BusinessSetting::TAX_MODE,
            BusinessSetting::DEFAULT_TAX_ID,
        ]);

        $taxEnabled = filter_var($settings->get(BusinessSetting::TAX_ENABLED, false), FILTER_VALIDATE_BOOLEAN);
        $taxMode = $settings->get(BusinessSetting::TAX_MODE, 'none') ?: 'none';
        $defaultTaxId = $settings->get(BusinessSetting::DEFAULT_TAX_ID);
        $defaultTaxId = $defaultTaxId === '' ? null : $defaultTaxId;

        $taxes = Tax::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'rate']);

        return Inertia::render('Admin/System/TaxSettings', [
            'settings' => [
                'tax_enabled' => $taxEnabled,
                'tax_mode' => $taxEnabled ? $taxMode : 'none',
                'default_tax_id' => $taxEnabled ? ($defaultTaxId ? (int) $defaultTaxId : null) : null,
            ],
            'taxes' => $taxes->map(fn (Tax $tax) => [
                'id' => $tax->id,
                'name' => $tax->name,
                'type' => $tax->type,
                'rate' => $tax->rate,
            ]),
        ]);
    }

    public function update(UpdateTaxSettingsRequest $request): \Illuminate\Http\RedirectResponse
    {
        Gate::authorize(Permissions::TAX_SETUP);

        $validated = $request->validated();
        $taxEnabled = (bool) $validated['tax_enabled'];
        $taxMode = $validated['tax_mode'];
        $defaultTaxId = $validated['default_tax_id'] ?? null;

        if (! $taxEnabled) {
            $taxMode = 'none';
            $defaultTaxId = null;
        } elseif ($taxMode !== 'global') {
            $defaultTaxId = null;
        }

        $this->businessSettingService->updateSettings([
            BusinessSetting::TAX_ENABLED => $taxEnabled ? '1' : '0',
            BusinessSetting::TAX_MODE => $taxMode,
            BusinessSetting::DEFAULT_TAX_ID => $defaultTaxId,
        ]);

        return back()->with('success', 'Tax settings updated successfully.');
    }
}
