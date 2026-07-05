# Development Workflow

## Start a Feature

```bash
git checkout develop
git pull origin develop
git checkout -b feature/<feature-name>
```

---

## Development Process

1. Analyze the feature.
2. Make architectural decisions.
3. Update documentation (if needed).
4. Implement.
5. Test with Bruno.
6. Self-review.

---

## Finish a Feature

```bash
git add .

git commit -m "<type>: <message>"

git push -u origin feature/<feature-name>

git checkout develop

git pull origin develop

git merge --no-ff feature/<feature-name>

git push origin develop

git branch -d feature/<feature-name>

git push origin --delete feature/<feature-name>
```