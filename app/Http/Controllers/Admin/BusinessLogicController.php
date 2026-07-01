<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessSettingsRequest;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BusinessLogicController extends Controller
{
    public function __construct(protected \App\Services\BusinessSettingService $businessSettingService) {}

    public function index()
    {
        Gate::authorize(Permissions::BUSINESS_LOGIC);

        $settings = $this->businessSettingService->getSettings([
            'currency_symbol',
            BusinessSetting::CURRENCY_POSITION,
            'language',
            BusinessSetting::INVOICE_PREFIX,
            BusinessSetting::INVOICE_TERMS,
        ]);

        return Inertia::render('Admin/Business/BusinessLogic', [
            'settings' => [
                'currency_symbol' => $settings->get('currency_symbol', '$'),
                'currency_position' => $settings->get(BusinessSetting::CURRENCY_POSITION, 'left'),
                'language' => $settings->get('language', 'en'),
                'invoice_prefix' => $settings->get(BusinessSetting::INVOICE_PREFIX, ''),
                'invoice_terms' => $settings->get(BusinessSetting::INVOICE_TERMS, ''),
            ],
        ]);
    }

    public function update(UpdateBusinessSettingsRequest $request)
    {
        Gate::authorize(Permissions::BUSINESS_LOGIC);

        $validated = $request->validated();

        $this->businessSettingService->updateSettings($validated);

        return back()->with('success', 'Business settings updated successfully.');
    }
}
