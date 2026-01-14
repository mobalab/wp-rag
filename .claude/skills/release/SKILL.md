# release

Release a new version of the WordPress plugin.

Usage: `/release <version>`

Example: `/release 0.8.0`

## Instructions

You must follow these steps to release a new version:

### 1. Create Release Branch

Create a new branch named `release-<version>` and switch to it:
```bash
git checkout -b release-<version>
```

### 2. Update Version Numbers

Update the version number in the following files:

**File: `wp-rag.php`**
- Line 8: `@version` (in the plugin header comment)
- Line 14: `Version:` (in the @wordpress-plugin section)
- Line 52: `WPRAG_VERSION` constant

**File: `readme.txt`**
- Line 10: `Stable tag:`

All version numbers must be updated to the same version (e.g., `0.8.0` without the `v` prefix).

After updating all version numbers, commit the changes:
```bash
git add wp-rag.php readme.txt
git commit -m "Bump version to <version>"
```

### 3. Update Changelog

In `readme.txt`, add a new changelog entry at the top of the `== Changelog ==` section.

To determine what changes to include:
1. Run `git log v<previous-version>..HEAD --oneline` to see commits since the last release
2. Analyze the commits and summarize the main changes
3. Format the entry as:
   ```
   = <version>: <Month Day>, <Year> =
   * <Change description 1>
   * <Change description 2>
   ```

After adding the changelog entry, commit it:
```bash
git add readme.txt
git commit -m "Update changelog for <version>"
```

### 4. Push and Create Pull Request

Push the release branch to origin:
```bash
git push origin release-<version>
```

Create a pull request with:
- **Title:** `Release <version>`
- **Description:** `Release <version>`

After creating the PR, display the PR URL to the user.

### 5. Wait for User Approval

Tell the user:
"The release PR has been created. Please review and merge it, then let me know when it's merged so I can create the tag."

Wait for the user to confirm the PR has been merged before proceeding.

### 6. Create and Push Tag

After the user confirms the PR is merged, run:
```bash
git checkout main
git pull origin main
git tag v<version>
git push origin v<version>
```

### 7. Create GitHub Release (Draft)

After creating and pushing the tag, create a draft GitHub release:

1. Read the changelog entry for the current version from `readme.txt`
2. Extract the changelog items (lines starting with `*`)
3. Create a release title by summarizing the main changes (semicolon-separated), prefixed with the version (e.g., "v0.8.0: Support for GPT-5; fix PHP warnings")
4. Create the draft release using:
   ```bash
   gh release create v<version> --draft --title "v<version>: <release-title>" --notes "<changelog-content>"
   ```

The release notes should include the changelog items from `readme.txt` formatted as a bulleted list.

After creating the draft release, inform the user that a draft release has been created and provide the release URL so they can review and publish it when ready.

## Notes

- Version numbers in files should NOT include the `v` prefix (use `0.8.0`, not `v0.8.0`)
- Git tags SHOULD include the `v` prefix (use `v0.8.0`)
- Always use the version number passed as an argument to this skill
- Ensure all version numbers are consistent across all files
