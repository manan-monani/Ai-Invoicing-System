<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LookupRequest;
use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LookupController extends Controller
{
    private const DEFAULT_LIMIT = 20;

    private const DEFAULT_EMPTY_LIMIT = 10;

    public function customers(LookupRequest $request): JsonResponse
    {
        $this->authorizeAny([Permissions::INVOICES, Permissions::CUSTOMER_DIRECTORY]);

        $term = $this->searchTerm($request);
        $limit = $this->limit($request, $term === '');

        $customers = User::query()
            ->where('type', UserType::CLIENT)
            ->where('status', 'active')
            ->when($term !== '', fn (Builder $query) => $this->applySearch($query, $term, ['name', 'email']))
            ->when($term === '', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($term !== '', fn (Builder $query) => $query->orderBy('name'))
            ->limit($limit)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $customers->map(fn (User $user) => [
                'value' => $user->id,
                'label' => sprintf('%s (%s)', $user->name, $user->email),
                'meta' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]),
        ]);
    }

    public function items(LookupRequest $request): JsonResponse
    {
        $this->authorizeAny([Permissions::INVOICES, Permissions::CATALOG_ITEMS]);

        $term = $this->searchTerm($request);
        $limit = $this->limit($request, $term === '');

        $items = Item::query()
            ->with(['category:id,tax_id'])
            ->where('is_active', true)
            ->when($term !== '', fn (Builder $query) => $this->applySearch($query, $term, ['name', 'sku']))
            ->when($term === '', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($term !== '', fn (Builder $query) => $query->orderBy('name'))
            ->limit($limit)
            ->get(['id', 'name', 'description', 'unit_price', 'tax_id', 'category_id', 'sku', 'has_discount', 'discount_amount', 'discount_is_percentage']);

        return response()->json([
            'data' => $items->map(fn (Item $item) => [
                'value' => $item->id,
                'label' => $item->name,
                'meta' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'has_discount' => $item->has_discount,
                    'discount_amount' => $item->discount_amount,
                    'discount_is_percentage' => $item->discount_is_percentage,
                    'tax_id' => $item->tax_id,
                    'category' => $item->category ? [
                        'id' => $item->category->id,
                        'tax_id' => $item->category->tax_id,
                    ] : null,
                ],
            ]),
        ]);
    }

    public function taxes(LookupRequest $request): JsonResponse
    {
        $this->authorizeAny([Permissions::CATALOG_TAXES, Permissions::CATALOG_ITEMS, Permissions::CATALOG_CATEGORIES, Permissions::TAX_SETUP]);

        $term = $this->searchTerm($request);
        $limit = $this->limit($request, $term === '');

        $taxes = Tax::query()
            ->when($term !== '', fn (Builder $query) => $this->applySearch($query, $term, ['name']))
            ->when($term === '', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($term !== '', fn (Builder $query) => $query->orderBy('name'))
            ->limit($limit)
            ->get(['id', 'name', 'rate', 'type']);

        return response()->json([
            'data' => $taxes->map(fn (Tax $tax) => [
                'value' => $tax->id,
                'label' => sprintf('%s (%s)', $tax->name, $tax->type === 'percentage' ? $tax->rate.'%' : '$'.$tax->rate),
                'meta' => [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                    'type' => $tax->type,
                ],
            ]),
        ]);
    }

    public function categories(LookupRequest $request): JsonResponse
    {
        $this->authorizeAny([Permissions::CATALOG_CATEGORIES, Permissions::CATALOG_ITEMS]);

        $term = $this->searchTerm($request);
        $limit = $this->limit($request, $term === '');

        $categories = Category::query()
            ->when($term !== '', fn (Builder $query) => $this->applySearch($query, $term, ['name']))
            ->when($term === '', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($term !== '', fn (Builder $query) => $query->orderBy('name'))
            ->limit($limit)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $categories->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->name,
                'meta' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ]),
        ]);
    }

    public function roles(LookupRequest $request): JsonResponse
    {
        $this->authorizeAny([Permissions::ACCESS_ROLES]);

        $term = $this->searchTerm($request);
        $limit = $this->limit($request, $term === '');

        $roles = Role::query()
            ->employeeAssignable()
            ->when($term !== '', fn (Builder $query) => $this->applySearch($query, $term, ['name']))
            ->when($term === '', fn (Builder $query) => $query->orderByDesc('created_at'))
            ->when($term !== '', fn (Builder $query) => $query->orderBy('name'))
            ->limit($limit)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $roles->map(fn (Role $role) => [
                'value' => $role->id,
                'label' => $role->name,
                'meta' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ]),
        ]);
    }

    private function authorizeAny(array $permissions): void
    {
        if (! Gate::any($permissions)) {
            Gate::authorize($permissions[0]);
        }
    }

    private function searchTerm(LookupRequest $request): string
    {
        return trim((string) ($request->validated()['q'] ?? ''));
    }

    private function limit(LookupRequest $request, bool $isEmpty): int
    {
        $limit = (int) ($request->validated()['limit'] ?? self::DEFAULT_LIMIT);

        if ($limit < 1) {
            $limit = self::DEFAULT_LIMIT;
        }

        $limit = min($limit, 50);

        if ($isEmpty) {
            return min($limit, self::DEFAULT_EMPTY_LIMIT);
        }

        return $limit;
    }

    private function applySearch(Builder $query, string $term, array $columns): void
    {
        $query->where(function (Builder $builder) use ($term, $columns) {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', "%{$term}%");
            }
        });
    }
}
