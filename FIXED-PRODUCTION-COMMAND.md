# Fixed Production Server Command

## ❌ The Problem

The command I gave you had an error:
```bash
git pull origin main --no-ff -m "Merge from GitHub"  # ❌ WRONG - git pull doesn't accept -m
```

## ✅ Corrected Command

Use this instead:

```bash
cd /home/s3vtgroup/public_html && \
git fetch origin && \
git stash && \
git merge origin/main --no-ff -m "Merge from GitHub" && \
git push origin main
```

**Or use the simpler version (Git will prompt for merge message):**

```bash
cd /home/s3vtgroup/public_html && \
git fetch origin && \
git stash && \
git pull origin main --no-ff && \
git push origin main
```

---

## 📋 Step-by-Step (Recommended)

```bash
# 1. Navigate to production directory
cd /home/s3vtgroup/public_html

# 2. Fetch latest from GitHub
git fetch origin

# 3. Stash any local changes (if any)
git stash

# 4. Merge with no fast-forward (fixes divergence)
git merge origin/main --no-ff -m "Merge from GitHub - $(date)"

# 5. Push the merged result
git push origin main

# 6. If you stashed changes and need them back:
git stash pop
```

---

## 🚀 One-Line Fixed Command

**Option 1: With merge message**
```bash
cd /home/s3vtgroup/public_html && git fetch origin && git stash && git merge origin/main --no-ff -m "Merge from GitHub" && git push origin main
```

**Option 2: Without merge message (Git will prompt)**
```bash
cd /home/s3vtgroup/public_html && git fetch origin && git stash && git pull origin main --no-ff && git push origin main
```

---

## 🔧 Why the Error Happened

- `git pull` = `git fetch` + `git merge`
- The `-m` flag works with `git merge`, not `git pull`
- When using `git pull --no-ff`, you can't specify the merge message directly

**Solution:** Use `git fetch` + `git merge` separately, or let Git prompt for the message.

---

## ✅ Use the Script Instead (Easiest)

The `fix-production-divergence.sh` script already has the correct commands. Just run:

```bash
bash fix-production-divergence.sh
```

This will handle everything correctly!
