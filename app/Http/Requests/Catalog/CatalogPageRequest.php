<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

final class CatalogPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'region' => ['nullable', 'integer', 'exists:regions,id'],
            'availability' => ['nullable', 'integer', 'exists:equipment_availabilities,id'],
            'state' => ['nullable', 'integer', 'exists:equipment_states,id'],
            'sort' => ['nullable', 'string', 'in:default,price_desc,price_asc'],
            'search' => ['nullable', 'string', 'max:100'],
            'category_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array{
     *     page: int,
     *     region: int|null,
     *     availability: int|null,
     *     state: int|null,
     *     sort: string,
     *     search: string|null,
     *     category_path: string|null
     * }
     */
    public function normalized(): array
    {
        $validated = $this->validated();

        return [
            'page' => (int) $validated['page'],
            'region' => $this->nullableInt($validated['region'] ?? null),
            'availability' => $this->nullableInt($validated['availability'] ?? null),
            'state' => $this->nullableInt($validated['state'] ?? null),
            'sort' => $validated['sort'],
            'search' => $validated['search'] ?? null,
            'category_path' => $validated['category_path'] ?? null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->normalizePage($this->input('page')),
            'region' => $this->emptyStringToNull($this->input('region')),
            'availability' => $this->emptyStringToNull($this->input('availability')),
            'state' => $this->emptyStringToNull($this->input('state')),
            'sort' => $this->normalizeSort($this->input('sort')),
            'search' => $this->normalizeSearch($this->input('search')),
            'category_path' => $this->normalizeCategoryPath($this->input('category_path')),
        ]);
    }

    private function normalizePage(mixed $value): mixed
    {
        return $this->isBlankString($value) || $value === null ? 1 : $value;
    }

    private function normalizeSort(mixed $value): mixed
    {
        return $this->isBlankString($value) || $value === null ? 'default' : $value;
    }

    private function normalizeSearch(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeCategoryPath(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);
        $value = trim($value, '/');

        return $value === '' ? null : $value;
    }

    private function emptyStringToNull(mixed $value): mixed
    {
        return $this->isBlankString($value) ? null : $value;
    }

    private function isBlankString(mixed $value): bool
    {
        return is_string($value) && trim($value) === '';
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }
}
