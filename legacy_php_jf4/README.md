# legacy_php_jf4

Purpose
- Store original JobFilter v4 (JF4) PHP files here as a reference for porting features and behaviors into the Laravel app.

Guidelines
- Do not execute or include legacy PHP files inside the Laravel runtime. Keep these files as read-only references.
- Preserve directory structure from the original JF4 project (controllers, views, includes, etc.) when you copy files here. That makes tracing easier.
- If you need to run the legacy app, run it separately in its own webroot / PHP environment (do not mix with this Laravel project).

Suggested subfolders
- `controllers/` — legacy controllers
- `views/` — legacy HTML/PHP views (templates)
- `includes/` — shared include files, helpers
- `assets/` — legacy CSS/JS/images (if needed)

Version control
- This folder can be added to the repo. If you plan to store large binary assets or uploaded files, consider using an external storage or `.gitignore` them.

When you're ready
- Copy your JF4 PHP files into this folder and let me know any specific files you want me to examine or port. I'll reference the original code to implement matching functionality in Laravel.
