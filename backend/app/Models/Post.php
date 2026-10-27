<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Models\Concerns\GeneratesSlug;
use App\Services\Cloudinary;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

#[Fillable([
    'category_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'author_name',
    'cover_image_alt',
    'status',
    'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use GeneratesSlug, HasFactory;

    private const WORDS_PER_MINUTE = 200;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if ($post->status === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }
        });
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The admin account that created the post.
     *
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    #[Scope]
    protected function draft(Builder $query): void
    {
        $query->where('status', PostStatus::Draft);
    }

    /**
     * Posts that are published and whose publish date has arrived.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PostStatus::Published)
            ->where('published_at', '<=', now());
    }

    /**
     * Published posts with a publish date still in the future.
     */
    #[Scope]
    protected function scheduled(Builder $query): void
    {
        $query->where('status', PostStatus::Published)
            ->where('published_at', '>', now());
    }

    public function isLive(): bool
    {
        return $this->status === PostStatus::Published && $this->published_at?->isPast();
    }

    public function isScheduled(): bool
    {
        return $this->status === PostStatus::Published && $this->published_at?->isFuture();
    }

    /**
     * Draft, Scheduled or Published, as shown to editors.
     */
    public function statusLabel(): string
    {
        return $this->isScheduled() ? 'Scheduled' : $this->status->label();
    }

    /**
     * The hand-written excerpt, or the opening of the body when there isn't one.
     */
    public function summary(int $limit = 180): string
    {
        return $this->excerpt ?: Str::limit($this->plainText(), $limit);
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count($this->plainText()) / self::WORDS_PER_MINUTE));
    }

    /**
     * The cover image resized with a Cloudinary transformation, e.g. "c_fill,w_800,h_450".
     */
    public function coverUrl(string $transformation): ?string
    {
        return $this->cover_image_url
            ? Cloudinary::transform($this->cover_image_url, $transformation)
            : null;
    }

    /**
     * Where the post lives on the public React site.
     */
    public function frontendUrl(): string
    {
        return config('app.frontend_url').'/blog/'.$this->slug;
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    private function plainText(): string
    {
        // Pad tags with spaces so "<p>one</p><p>two</p>" doesn't become "onetwo".
        return Str::squish(html_entity_decode(strip_tags(str_replace('<', ' <', $this->body ?? ''))));
    }

    /**
     * Clean editor HTML on the way in; the React site renders it as-is.
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null
                ? null
                : app(HtmlSanitizerInterface::class)->sanitize($value),
        );
    }
}
