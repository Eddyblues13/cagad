<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class PostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique(Post::class)->ignore($this->route('post'))],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'author_name' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'required_if:status,'.PostStatus::Published->value],
            'cover_image' => ['nullable', File::image()->max('5mb')],
            'cover_image_alt' => ['nullable', 'string', 'max:255'],
            'remove_cover' => ['nullable', 'boolean'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required_if' => 'Write the post before publishing it, or save it as a draft.',
            'slug.unique' => 'Another post already uses this URL slug.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'cover_image' => 'cover image',
            'cover_image_alt' => 'image description',
            'published_at' => 'publish date',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
    }
}
