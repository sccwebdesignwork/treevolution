# Upload the complete Treevolution repository on Windows

The safest method is to initialise the extracted folder as a Git repository, then publish/push it. Git tracks dotfiles such as `.github/`, `.gitignore` and `.htaccess` normally.

## Before you start

Extract the full repository ZIP to a normal folder such as:

`C:\Websites\treevolution`

Do not upload the ZIP file itself.

In PowerShell, confirm the important dotfiles are present:

```powershell
cd C:\Websites\treevolution
Get-ChildItem -Force
```

You should see `.github`, `.gitignore`, `.htaccess`, `assets`, `services`, `client-update`, `index.html`, and the other project files.

## Initialise the local repository

If Git is installed:

```powershell
cd C:\Websites\treevolution
git init -b main
git add .
git status
git commit -m "Initial Treevolution V6.4 staging site"
```

Check `git status` before committing and confirm `.github/workflows/deploy.yml`, `.gitignore` and `.htaccess` are included. Never commit secrets, passwords, PATs or API keys.

## Publish with GitHub Desktop

Open GitHub Desktop and sign in to the `sccwebdesignwork` account. Choose **File → Add local repository** and select `C:\Websites\treevolution`. Then click **Publish repository**, use the name `treevolution`, keep it **Private**, and publish it to `sccwebdesignwork`.

If you already created an empty `sccwebdesignwork/treevolution` repository on GitHub, add it as the remote and push instead:

```powershell
git remote add origin https://github.com/sccwebdesignwork/treevolution.git
git push -u origin main
```

## Important

The local `.git/` directory is Git's internal metadata and is not uploaded as a normal repository file. That is correct. The dotfiles we do need in GitHub are `.github/`, `.gitignore`, `.htaccess`, and `client-update/.htaccess`.
