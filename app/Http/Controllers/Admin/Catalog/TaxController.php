<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreTaxRequest;
use App\Http\Requests\Admin\Catalog\UpdateTaxRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ItemResource;
use App\Http\Resources\TaxResource;
use App\Models\Category;
use App\Models\Item;
use App\Models\Tax;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaxController extends Controller
{
    public function __construct(protected TaxService $taxService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::CATALOG_TAXES);

        $sort = request('sort', 'newest');
        $perPage = (int) request('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $search = trim((string) request('search', ''));

        $taxes = Tax::query()
            ->withCount(['categories', 'items'])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($sort === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('name'))
            ->when(! in_array($sort, ['oldest', 'newest', 'name_asc', 'name_desc'], true), fn ($query) => $query->orderByDesc('created_at'))
            ->paginate($perPage)
            ->withQueryString();

        $taxCategoriesId = request('tax_categories_id');
        $taxItemsId = request('tax_items_id');
        $taxCategories = null;
        $taxItems = null;
        $taxCategoriesTax = null;
        $taxItemsTax = null;

        if ($taxCategoriesId) {
            $taxCategoriesTax = Tax::query()
                ->select('id', 'name')
                ->find($taxCategoriesId);

            if ($taxCategoriesTax) {
                $taxCategories = Category::query()
                    ->where('tax_id', $taxCategoriesTax->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        if ($taxItemsId) {
            $taxItemsTax = Tax::query()
                ->select('id', 'name')
                ->find($taxItemsId);

            if ($taxItemsTax) {
                $taxItems = Item::query()
                    ->where('tax_id', $taxItemsTax->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return Inertia::render('Admin/Catalog/Taxes/Index', [
            'taxes' => TaxResource::collection($taxes),
            'taxes_count' => $taxes->total(),
            'tax_categories' => $taxCategories ? CategoryResource::collection($taxCategories) : null,
            'tax_categories_tax' => $taxCategoriesTax,
            'tax_items' => $taxItems ? ItemResource::collection($taxItems) : null,
            'tax_items_tax' => $taxItemsTax,
            'filters' => [
                'sort' => $sort,
                'per_page' => $perPage,
                'tax_categories_id' => $taxCategoriesTax?->id,
                'tax_items_id' => $taxItemsTax?->id,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_TAXES);

        $this->taxService->create($request->validated());

        return back()->with('success', 'Tax created successfully.');
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_TAXES);

        $this->taxService->update($tax, $request->validated());

        return back()->with('success', 'Tax updated successfully.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        Gate::authorize(Permissions::CATALOG_TAXES);

        $this->taxService->delete($tax);

        return back()->with('success', 'Tax deleted successfully.');
    }
}
