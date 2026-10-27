{{-- Shared by create and edit. Expects $category, $action and $method. --}}
<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="{{ $action }}" class="admin-card" novalidate>
            @csrf
            @method($method)

            <div class="admin-card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                           @class(['form-control', 'is-invalid' => $errors->has('name')])
                           maxlength="100" required autofocus placeholder="e.g. Policy Briefs">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug <span class="optional">(optional)</span></label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug) }}"
                           @class(['form-control', 'is-invalid' => $errors->has('slug')])
                           maxlength="120" placeholder="built-from-the-name" aria-describedby="slug-help">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="slug-help" class="form-text">Used in links that filter the blog. Leave empty to build it from the name.</div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                        <label for="description" class="form-label">Description <span class="optional">(optional)</span></label>
                        <span class="char-count" id="description-count"></span>
                    </div>
                    <textarea id="description" name="description" rows="4" maxlength="500" data-char-count="description-count"
                              @class(['form-control', 'is-invalid' => $errors->has('description')])>{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="admin-card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save category
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="notice notice-info mb-0">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>
                Only categories with at least one published post appear on the public blog, so you can set topics up before writing for them.
            </div>
        </div>
    </div>
</div>
