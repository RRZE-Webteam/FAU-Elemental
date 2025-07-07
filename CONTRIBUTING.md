# Contributing Guidelines

Thank you for your interest in contributing to **FAU-Elemental**! We welcome your support and ideas. To ensure smooth collaboration within the team, please follow these guidelines carefully.

## 1. Branching Strategy

- **Always branch off `dev`** when starting a new feature.  
  Do **not** use other branches, even if they contain useful features.
- **Never merge unfinished feature branches** into your branch.  
  If you require such features, contact the technical lead (@MManthey) to discuss alternatives.
- To create a new branch:
  ```bash
  git checkout dev
  git checkout -b <username>/<feature-name>
  ```
  If your branch is related to an issue, use:
  ```bash
  <username>/<issue-number>/<feature-name>
  ```
- You are responsible for your branches. Keep them clean and **delete them** when no longer needed.

## 2. Code Quality & Formatting

- Run the following commands **regularly**:
  ```bash
  npm run format
  npm run fix:css
  ```
- Always follow the FAU rules for Wordpress Themes and Gutenberg Blocks:
  - [Vorgaben an Themes](https://www.wp.rrze.fau.de/entwicklung/einsatz-fremdplugins/vorgaben-an-themes/)
  - [Vorgaben an Blöcke](https://www.wp.rrze.fau.de/entwicklung/einsatz-fremdplugins/vorgaben-an-bloecke/)
- Before opening a Pull Request:
  1. Merge the latest `dev` into your branch. (See "4. Merging `dev` into Your Branch")
  2. Run:
     ```bash
     npm run pr-check
     ```
  3. Only create the PR if **all tests pass** and **no errors or warnings remain**.

## 3. Pull Requests

- Use a **descriptive title**. If the PR is tied to an issue, prefix it with the issue number:
  ```
  [11] Big Buttons Functionality
  [18] Facts Grid Styling
  ```
- **Do not** include status comments like “ready to style” in the title.
- If the PR is linked to an issue:
  - Link the issue in the **Development** section.
  - Assign the **same Milestone** as the issue.
- **Request a review** from at least:
  - @MManthey
  - @NHollmann
- Add **appropriate labels** to your PR to improve clarity and tracking.

## 4. Merging `dev` into Your Branch

- If you are **working alone** on a branch:
  ```bash
  git rebase dev
  git push --force
  ```
  ⚠️ Only use `rebase` if no one else is working on the branch. It rewrites history and requires force-pushing.
- If rebasing is problematic or you’re collaborating with others, use a standard merge instead:
  ```bash
  git merge dev
  ```

## 5. Scope of Pull Requests

- Keep PRs **small and focused**.
- Avoid combining **functionality and styling** in the same PR, especially if styling will be done by someone else.


Thanks again for contributing and helping us improve FAU-Elemental!
