# CAGAD blog backend

Laravel 13 JSON API for the CAGAD blog. It has no pages of its own:

- **Public API** at `/api/v1`, which the React site reads for `/blog` and the homepage.
- **Admin API** at `/api/v1/admin`, used by the blog admin at `/admin` in the React site (`src/admin` in the repo root): dashboard, posts (write, schedule, publish), categories and admin accounts.

Opening this app's own `/` or `/admin` redirects to `FRONTEND_URL/admin`. There is no Node/Vite build step here.

## Environment

Copy `.env.example` to `.env` (`composer setup` does this for you) and fill in:

| Variable | Purpose |
| --- | --- |
| `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET` | Cloudinary credentials, from the Cloudinary dashboard |
| `CLOUDINARY_FOLDER` | Folder that blog images go into. Defaults to `cagad/blog`; covers land in `…/covers`, in-post images in `…/content` |
| `FRONTEND_URL` | The React site, e.g. `https://cagad.org`. Used for post links and to send old `/admin` links to the React admin |
| `CORS_ALLOWED_ORIGINS` | Comma-separated origins allowed to call the API. `*` works because admin requests carry a bearer token, not cookies; `https://cagad.org,https://www.cagad.org` is tighter |
| `APP_TIMEZONE` | Defaults to `Africa/Lagos`; publish dates in the admin are entered in this zone |
| `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | The super admin that `db:seed` creates. Defaults to `CAGAD Admin` and `admin@cagad.org`. With no password set it uses `password` locally, and anywhere else a random one that is printed once |
| `DB_*` | MySQL. Locally the `cagad` database on XAMPP (user `root`, no password); on the server, the database and user created in the host's control panel |

`.env` is gitignored. Never commit the Cloudinary secret.

## Commands

```bash
php artisan db:seed           # default super admin, categories and sample posts (safe to re-run)
php artisan admin:create      # create a super admin, or reset any admin's password
php artisan cloudinary:check  # verify the Cloudinary credentials
php artisan test              # run the tests (they never call the real Cloudinary)
```

Locally, `db:seed` gives you the login `admin@cagad.org` / `password`; sign in at http://localhost:5173/admin with the React dev server running.

`db:seed` also adds ten sample posts: eight published and two scheduled a few days ahead, which go live on their own. They are only added while the blog has no posts at all, so re-seeding never brings back a sample you deleted or mixes samples in with real articles. Locally you also get two unfinished drafts for trying out the admin panel.

## Admin accounts

Admins live in their own `admins` table and sign in through a separate `admin` guard that checks bearer tokens (see `config/auth.php` and `App\Models\AdminToken`). The framework's `users` table is not used for admin access. There is no public sign-up.

| Role | Can |
| --- | --- |
| Super Admin | Everything an editor can, plus add, edit, deactivate and remove admins under **Admins** |
| Editor | Write, schedule and publish posts, manage categories, and edit their own account |

- Signing in returns a token that lasts 24 hours, or 30 days with "Keep me signed in". Only a SHA-256 hash of it is stored (`admin_tokens`), and signing out deletes that browser's token.
- Two middleware protect the admin API. `admin` (`AuthenticateAdmin`) only lets through tokens of active admins and answers 401 otherwise, which signs the React admin out. `admin.role:super_admin` (`EnsureAdminHasRole`) guards the **Admins** endpoints.
- Deactivating an admin, or resetting their password, signs them out on every device. Removing an admin keeps their posts.
- Nobody can change their own role, deactivate or delete themselves, so there is always an active super admin. Everyone edits their own name, email and password under **My account**.
- Sign-in locks out an email and IP pair for a minute after 5 failed attempts. Emails are not case-sensitive.
- There is no email-based password reset. A super admin resets passwords under **Admins**, or run `php artisan admin:create --email=…` on the server.

## How posts and images work

- **Status.** A post is a *Draft* or *Published*. A published post whose publish date is in the future is shown as *Scheduled* and appears on the site automatically when that time arrives. No cron job is needed.
- **Cover images** are uploaded to Cloudinary when the post is saved. Replacing or removing a cover, or deleting the post, deletes the old image from Cloudinary.
- **Images inside a post** are uploaded the moment they're added or pasted into the editor, and capped at 1600 px wide. They aren't deleted automatically when a post is deleted.
- **Post HTML is sanitised on save** (scripts, event handlers, `javascript:` links and iframes are stripped). This is what makes it safe for the React site to render the body as HTML.
- **Uploads are limited to 5 MB** per image. Make sure PHP allows that: `upload_max_filesize = 8M` and `post_max_size = 16M` in `php.ini` (the PHP default of 2 MB is too small).

## API

Every endpoint is rate-limited to 120 requests per minute per IP. The public ones are `GET` only:

| Endpoint | Returns |
| --- | --- |
| `/api/v1/posts` | Published posts, newest first, paginated. Query: `page`, `per_page` (max 24, default 9), `category` (slug), `search` |
| `/api/v1/posts/{slug}` | One published post including `body`, plus up to three `related` posts. Drafts and scheduled posts return 404 |
| `/api/v1/categories` | Categories that have at least one published post, with `posts_count` |

Each post includes `cover_image` with ready-made Cloudinary renditions (`thumbnail`, `card`, `hero`) that are already resized and served as WebP or AVIF where the browser supports it.

The admin endpoints need `Authorization: Bearer <token>`, except login:

| Endpoint | Does |
| --- | --- |
| `POST /api/v1/admin/login` | `email`, `password`, `remember` → `token`, `expires_at`, `admin` |
| `POST /api/v1/admin/logout` | Ends this token |
| `GET /api/v1/admin/dashboard` | Stats, posts per month, recent and scheduled posts, top categories |
| `/api/v1/admin/posts`, `/categories`, `/admins` | REST resources: list, create, show, update, delete. Posts can be filtered by `q`, `category` and `status`. `admins` is for super admins only |
| `GET` and `PUT /api/v1/admin/account` | The signed-in admin's own details and password |
| `POST /api/v1/admin/uploads/images` | An image from the post editor → `location` |

Validation errors come back as the usual Laravel 422 JSON. Post edits with a new cover image are sent as a multipart `POST` with `_method=PUT`, since PHP only reads uploaded files on POST.

## Deploying

1. Point the web server's document root at `backend/public`.
2. Set the environment variables above, plus `APP_ENV=production`, `APP_DEBUG=false` and `APP_URL`.
3. Run:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate        # first deploy only
   php artisan migrate --force
   php artisan db:seed --force     # first deploy only: super admin, default categories and sample posts
   php artisan optimize
   php artisan cloudinary:check
   ```

   Set `ADMIN_EMAIL` (and ideally `ADMIN_PASSWORD`) before seeding. If `ADMIN_PASSWORD` is empty, the seeder generates a password and prints it once; sign in and change it straight away.

4. In Vercel, set `VITE_API_URL` on the frontend project to this app's URL and redeploy. The admin is then at `https://cagad.org/admin`.
