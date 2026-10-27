<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Fills an empty "slug" column from another attribute when the model is saved,
 * appending -2, -3, … until the slug is unique.
 *
 * @mixin Model
 */
trait GeneratesSlug
{
    /**
     * The attribute the slug is derived from.
     */
    abstract protected function slugSource(): string;

    protected static function bootGeneratesSlug(): void
    {
        static::saving(function (Model $model): void {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlugFrom((string) $model->getAttribute($model->slugSource()));
            }
        });
    }

    protected function uniqueSlugFrom(string $value): string
    {
        $base = Str::slug($value) ?: Str::slug(class_basename($this));
        $slug = $base;

        for ($suffix = 2; $this->slugIsTaken($slug); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    private function slugIsTaken(string $slug): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn (Builder $query) => $query->whereKeyNot($this->getKey()))
            ->exists();
    }
}
