# Treevolution client update

1. Copy `config.example.php` outside the public web root as `treevolution-config.php`.
2. Replace the password hash and GitHub details. Use a fine-grained GitHub PAT restricted to this one repository with Contents: Read/Write.
3. Protect `/client-update/` with HTTPS and preferably an additional hosting-level password/IP rule.
4. Do not commit the real config file or PAT.
5. Submissions write to `content/uploads/` and `content/stories.json`, creating a GitHub commit.

The front-end can later render `stories.json`, or a build step can turn stories into static pages.
