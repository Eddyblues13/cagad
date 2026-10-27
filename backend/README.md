# CAGAD blog backend

Laravel 13 app with two jobs:

- **Admin panel** at `/admin`: a dashboard, posts (write, schedule, publish), categories and admin accounts.
- **Public JSON API** at `/api/v1`, which the React site in the repo root reads.

The admin pages load Bootstrap, Font Awesome and the TinyMCE editor from public CDNs, so this app has no Node/Vite build step.

## Environment

Copy `.env.example` to `.env` (`composer setup` does this for you) and fill in:

| Variable | Purpose |
| --- | --- |
| `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET` | Cloudinary credentials, from the Cloudinary dashboard |
| `CLOUDINARY_FOLDER` | Folder that blog images go into. Defaults to `cagad/blog`; covers land in `…/covers`, in-post images in `…/content` |
| `FRONTEND_URL` | The React site, e.g. `https://cagad.org`. Used for "View on site" links |
| `CORS_ALLOWED_ORIGINS` | Comma-separated origins allowed to call the API. `*` is fine since the API is public and read-only |
| `APP_TIMEZONE` | Defaults to `Africa/Lagos`; publish dates in the admin are entered in this zone |
| `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | The super admin that `db:seed` creates. Defaults to `CAGAD Admin` and `admin@cagad.org`. With no password set it uses `password` locally, and anywhere else a random one that is printed once |
| `DB_*` | SQLite works locally; use MySQL or PostgreSQL in production |

`.env` is gitignored. Never commit the Cloudinary secret.

## Commands

```bash
php artisan db:seed           # default super admin and categories, plus sample posts locally (safe to re-run)
php artisan admin:create      # create a super admin, or reset any admin's password
php artisan cloudinary:check  # verify the Cloudinary credentials
php artisan test              # run the tests (they never call the real Cloudinary)
```

Locally, `db:seed` gives you the login `admin@cagad.org` / `password`. Sample posts are only seeded when `APP_ENV=local`, so they can't reach the live blog.

## Admin accounts

Admins live in their own `admins` table and sign in through a separate `admin` session guard (see `config/auth.php`). The framework's `users` table is not used for admin access. There is no public sign-up.

| Role | Can |
| --- | --- |
| Super Admin | Everything an editor can, plus add, edit, deactivate and remove admins under **Admins** |
| Editor | Write, schedule and publish posts, manage categories, and edit their own account |

- Two middleware protect the panel. `admin` (`AuthenticateAdmin`) only lets in signed-in, active admins. `admin.role:super_admin` (`EnsureAdminHasRole`) guards the **Admins** section.
- Deactivating an admin blocks sign-in and signs them out on their next request. Removing an admin keeps their posts.
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

All endpoints are `GET`, public, and rate-limited to 120 requests per minute per IP.

| Endpoint | Returns |
| --- | --- |
| `/api/v1/posts` | Published posts, newest first, paginated. Query: `page`, `per_page` (max 24, default 9), `category` (slug), `search` |
| `/api/v1/posts/{slug}` | One published post including `body`, plus up to three `related` posts. Drafts and scheduled posts return 404 |
| `/api/v1/categories` | Categories that have at least one published post, with `posts_count` |

Each post includes `cover_image` with ready-made Cloudinary renditions (`thumbnail`, `card`, `hero`) that are already resized and served as WebP or AVIF where the browser supports it.

## Deploying

1. Point the web server's document root at `backend/public`.
2. Set the environment variables above, plus `APP_ENV=production`, `APP_DEBUG=false` and `APP_URL`.
3. Run:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate        # first deploy only
   php artisan migrate --force
   php artisan db:seed --force     # first deploy only: super admin and default categories
   php artisan optimize
   php artisan cloudinary:check
   ```

   Set `ADMIN_EMAIL` (and ideally `ADMIN_PASSWORD`) before seeding. If `ADMIN_PASSWORD` is empty, the seeder generates a password and prints it once; sign in and change it straight away.

4. In Vercel, set `VITE_API_URL` on the frontend project to this app's URL and redeploy.
