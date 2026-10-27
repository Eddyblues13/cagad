{{-- Shared by create and edit. Expects $post, $categories, $action and $method. --}}
@php($status = old('status', $post->status?->value))

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="post-form" novalidate data-unsaved-warning>
    @csrf
    @method($method)

    @if ($errors->any())
        <div class="notice notice-danger" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <div><strong>The post wasn’t saved.</strong> Fix the highlighted fields and try again.</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card mb-4">
                <div class="admin-card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}"
                               @class(['form-control title-input', 'is-invalid' => $errors->has('title')])
                               maxlength="255" required placeholder="A clear, specific headline" @unless ($post->exists) autofocus @endunless>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label">URL</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text d-none d-sm-flex">{{ Str::after(config('app.frontend_url'), '://') }}/blog/</span>
                            <span class="input-group-text d-sm-none">/blog/</span>
                            <input type="text" id="slug" name="slug" value="{{ old('slug', $post->slug) }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('slug')])
                                   maxlength="255" placeholder="generated-from-the-title" aria-describedby="slug-help">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="slug-help" class="form-text">
                            Leave empty to build it from the title.
                            @if ($post->isLive())
                                <strong>This post is live, so changing the URL breaks links that were already shared.</strong>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-baseline gap-2">
                            <label for="excerpt" class="form-label">Excerpt <span class="optional">(optional)</span></label>
                            <span class="char-count" id="excerpt-count"></span>
                        </div>
                        <textarea id="excerpt" name="excerpt" rows="3" maxlength="500" data-char-count="excerpt-count"
                                  @class(['form-control', 'is-invalid' => $errors->has('excerpt')])
                                  aria-describedby="excerpt-help">{{ old('excerpt', $post->excerpt) }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="excerpt-help" class="form-text">Shown on blog cards and in search results. Leave empty to use the opening of the post.</div>
                    </div>
                </div>
            </div>

            <div class="admin-card editor-card">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title"><label for="body" class="m-0">Content</label></h2>
                        <p class="admin-card-subtitle">Images you add or paste are uploaded to Cloudinary automatically.</p>
                    </div>
                </div>
                @error('body')
                    <div class="notice notice-danger m-3 mb-0" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <div>{{ $message }}</div>
                    </div>
                @enderror
                <textarea id="body" name="body" rows="20" class="form-control">{{ old('body', $post->body) }}</textarea>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-4">
                <section class="admin-card" aria-labelledby="publish-title">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title" id="publish-title">Publish</h2>
                    </div>
                    <div class="admin-card-body">
                        <fieldset class="mb-3">
                            <legend class="form-label">Status</legend>
                            <div class="segmented">
                                @foreach (\App\Enums\PostStatus::cases() as $case)
                                    <input type="radio" class="btn-check" name="status" id="status-{{ $case->value }}" value="{{ $case->value }}" autocomplete="off" @checked($status === $case->value)>
                                    <label class="btn" for="status-{{ $case->value }}">
                                        <i @class(['fa-regular fa-file-lines' => $case === \App\Enums\PostStatus::Draft, 'fa-solid fa-globe' => $case !== \App\Enums\PostStatus::Draft]) aria-hidden="true"></i>
                                        {{ $case->label() }}
                                    </label>
                                @endforeach
                            </div>
                            @error('status')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </fieldset>

                        <div class="mb-3">
                            <label for="published_at" class="form-label">Publish date <span class="optional">(optional)</span></label>
                            <input type="datetime-local" id="published_at" name="published_at"
                                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('published_at')])
                                   aria-describedby="published-at-help">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="published-at-help" class="form-text">Empty means now. A future date schedules a published post. Times are Lagos time (WAT).</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> {{ $post->exists ? 'Save changes' : 'Save post' }}
                        </button>
                        <p class="form-text text-center mb-0 mt-2">Tip: press <kbd>Ctrl</kbd> + <kbd>S</kbd> (<kbd>⌘</kbd> + <kbd>S</kbd> on a Mac) to save.</p>
                    </div>
                </section>

                <section class="admin-card" aria-labelledby="organise-title">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title" id="organise-title">Organise</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id" name="category_id" @class(['form-select', 'is-invalid' => $errors->has('category_id')])>
                                <option value="">Uncategorised</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="author_name" class="form-label">Author</label>
                            <input type="text" id="author_name" name="author_name" value="{{ old('author_name', $post->author_name) }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('author_name')])
                                   maxlength="120" aria-describedby="author-help">
                            @error('author_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="author-help" class="form-text">The byline readers see, e.g. a fellow or a guest writer.</div>
                        </div>
                    </div>
                </section>

                <section class="admin-card" aria-labelledby="cover-title">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title" id="cover-title">Cover image</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="cover-field mb-3">
                            <input type="file" id="cover_image" name="cover_image" class="visually-hidden"
                                   accept="image/jpeg,image/png,image/webp,image/gif" aria-describedby="cover-help">
                            <label for="cover_image" id="cover-drop" @class(['cover-drop', 'has-image' => $post->cover_image_url])>
                                @if ($post->cover_image_url)
                                    <img src="{{ $post->coverUrl('c_fill,g_auto,w_640,h_400') }}" alt="">
                                    <span class="visually-hidden">Replace the cover image</span>
                                @else
                                    <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                                    <span><strong>Click to upload</strong> or drag an image here</span>
                                    <span class="small">JPG, PNG, WebP or GIF up to 5 MB</span>
                                @endif
                            </label>
                            @error('cover_image')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            <div id="cover-help" class="form-text">Landscape images around 1600 × 900 look best.</div>
                        </div>

                        <div @class(['mb-3' => $post->cover_image_url])>
                            <label for="cover_image_alt" class="form-label">Image description</label>
                            <input type="text" id="cover_image_alt" name="cover_image_alt" value="{{ old('cover_image_alt', $post->cover_image_alt) }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('cover_image_alt')])
                                   maxlength="255" aria-describedby="alt-help">
                            @error('cover_image_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="alt-help" class="form-text">Describes the image for screen readers and search engines.</div>
                        </div>

                        @if ($post->cover_image_url)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_cover" id="remove_cover" value="1" @checked(old('remove_cover'))>
                                <label class="form-check-label" for="remove_cover">Remove the cover image</label>
                            </div>
                        @endif
                    </div>
                </section>

                @if ($post->exists)
                    <section class="admin-card" aria-labelledby="details-title">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title" id="details-title">Details</h2>
                        </div>
                        <div class="admin-card-body">
                            <dl class="meta-list">
                                <div><dt>Created by</dt><dd>{{ $post->admin?->name ?? '—' }}</dd></div>
                                <div><dt>Created</dt><dd>{{ $post->created_at->format('j M Y, H:i') }}</dd></div>
                                <div><dt>Last saved</dt><dd>{{ $post->updated_at->diffForHumans() }}</dd></div>
                                <div><dt>Reading time</dt><dd>{{ $post->readingTime() }} min</dd></div>
                            </dl>
                        </div>
                    </section>

                    <section class="admin-card" aria-labelledby="delete-title">
                        <div class="admin-card-body">
                            <h2 class="admin-card-title mb-1" id="delete-title">Delete post</h2>
                            <p class="small text-secondary mb-3">Removes the post and its cover image. This can’t be undone.</p>
                            {{-- Submits the separate delete form below the editor; forms can't be nested. --}}
                            <button type="submit" form="delete-post-form" class="btn btn-outline-danger btn-sm w-100">
                                <i class="fa-regular fa-trash-can" aria-hidden="true"></i> Delete post
                            </button>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.9.1/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (() => {
            const form = document.getElementById('post-form');
            const uploadUrl = @js(route('admin.uploads.images'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const markDirty = () => form.dispatchEvent(new Event('input', { bubbles: true }));

            // Images pasted or inserted into the editor go straight to Cloudinary.
            const uploadImage = (blobInfo, progress) => new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', uploadUrl);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.onprogress = (event) => progress(event.loaded / event.total * 100);
                xhr.onerror = () => reject({ message: 'Image upload failed: network error.', remove: true });
                xhr.onload = () => {
                    let json = {};
                    try { json = JSON.parse(xhr.responseText); } catch { /* not JSON */ }

                    if (xhr.status >= 200 && xhr.status < 300 && json.location) {
                        resolve(json.location);
                        return;
                    }

                    const message = json.errors?.file?.[0] ?? json.message ?? `HTTP ${xhr.status}`;
                    reject({ message: `Image upload failed: ${message}`, remove: true });
                };

                const data = new FormData();
                data.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(data);
            });

            tinymce.init({
                selector: '#body',
                license_key: 'gpl',
                promotion: false,
                branding: false,
                menubar: false,
                plugins: 'autolink autoresize link lists image table code wordcount',
                toolbar: 'undo redo | blocks | bold italic underline | bullist numlist blockquote | link image table | removeformat code',
                toolbar_mode: 'sliding',
                toolbar_sticky: true,
                toolbar_sticky_offset: 64,
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                min_height: 520,
                autoresize_bottom_margin: 40,
                // The autoresize plugin sets the body's side padding inline, overriding content_style.
                autoresize_overflow_padding: 24,
                content_css: ['default', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap'],
                image_caption: true,
                image_dimensions: false,
                automatic_uploads: true,
                paste_data_images: true,
                images_file_types: 'jpg,jpeg,png,gif,webp',
                images_upload_handler: uploadImage,
                relative_urls: false,
                convert_urls: false,
                link_assume_external_targets: 'https',
                link_target_list: [{ title: 'Same tab', value: '' }, { title: 'New tab', value: '_blank' }],
                content_style: `
                    body { font-family: 'Open Sans', system-ui, sans-serif; font-size: 17px; line-height: 1.75; color: #333; max-width: 760px; margin: 1.25rem auto; }
                    h2, h3, h4 { font-family: Montserrat, sans-serif; color: #003366; }
                    img { max-width: 100%; height: auto; border-radius: 8px; }
                    figure { margin: 1.5rem 0; }
                    figcaption { font-size: 14px; color: #6b7280; text-align: center; }
                    blockquote { border-left: 4px solid #006633; margin-left: 0; padding-left: 1rem; color: #4b5563; font-style: italic; }
                    table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #ddd; padding: 6px 10px; }
                `,
                setup: (editor) => {
                    editor.on('input change undo redo', markDirty);
                    editor.addShortcut('meta+s', 'Save post', () => form.requestSubmit());
                },
            });

            // Don't save while an image is still uploading, or it would be lost.
            form.addEventListener('submit', (event) => {
                const editor = tinymce.get('body');
                if (!editor || !editor.getBody().querySelector('img[src^="blob:"], img[src^="data:"]')) {
                    return;
                }

                event.preventDefault();
                editor.uploadImages().then(() => {
                    editor.save();
                    form.submit();
                });
            });

            // Ctrl/Cmd + S saves from anywhere on the page.
            document.addEventListener('keydown', (event) => {
                if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    form.requestSubmit();
                }
            });

            // Cover image: preview on pick, and accept an image dropped on the box.
            const coverInput = document.getElementById('cover_image');
            const coverDrop = document.getElementById('cover-drop');

            coverInput.addEventListener('change', () => {
                const [file] = coverInput.files;
                if (!file) {
                    return;
                }

                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = '';
                const label = document.createElement('span');
                label.className = 'visually-hidden';
                label.textContent = 'Replace the cover image';
                coverDrop.replaceChildren(img, label);
                coverDrop.classList.add('has-image');
            });

            ['dragenter', 'dragover'].forEach((type) => coverDrop.addEventListener(type, (event) => {
                event.preventDefault();
                coverDrop.classList.add('is-dragover');
            }));

            ['dragleave', 'drop'].forEach((type) => coverDrop.addEventListener(type, () => {
                coverDrop.classList.remove('is-dragover');
            }));

            coverDrop.addEventListener('drop', (event) => {
                event.preventDefault();
                const [file] = event.dataTransfer.files;
                if (!file || !file.type.startsWith('image/')) {
                    return;
                }

                const transfer = new DataTransfer();
                transfer.items.add(file);
                coverInput.files = transfer.files;
                coverInput.dispatchEvent(new Event('change', { bubbles: true }));
            });

            // Show the slug the title will produce while the field is empty.
            const title = document.getElementById('title');
            const slug = document.getElementById('slug');
            const slugify = (value) => value.normalize('NFKD').replace(/[̀-ͯ]/g, '')
                .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            title.addEventListener('input', () => {
                slug.placeholder = slugify(title.value) || 'generated-from-the-title';
            });
        })();
    </script>
@endpush
