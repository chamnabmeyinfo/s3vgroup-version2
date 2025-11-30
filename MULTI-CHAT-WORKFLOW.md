# Multi-Chat Workflow Guide

## Your 3 Specialized AI Chats

### 1. **Main Development Chat** (General Project)
**Purpose:** Build features, fix bugs, general development
**When to use:** Daily development work
**Workflow:** Direct coding and implementation

### 2. **Backend Planning Chat** (Planning & Architecture)
**Purpose:** Understand requirements, plan features, get approval before coding
**When to use:** Starting new features, complex requirements, architecture decisions
**Workflow:** Understand → Ask Questions → Plan → Get Approval → Implement

### 3. **Agent Review Chat** (Code Quality & Security)
**Purpose:** Review code for security, bugs, quality, best practices
**When to use:** After coding, before deployment, periodic audits
**Workflow:** Review → Find Issues → Explain Root Causes → Fix

---

## Recommended Workflow

### Scenario 1: Building a New Feature

```
Step 1: Backend Planning Chat
├─ Explain: "I want to build user authentication"
├─ AI asks priority questions
├─ You answer questions
├─ AI creates detailed plan
└─ You approve: "Approve"

Step 2: Main Development Chat (or Backend Planning continues)
├─ AI implements the approved plan
├─ Creates all necessary files
└─ Feature is built

Step 3: Agent Review Chat
├─ You: "Review the authentication system I just built"
├─ AI reviews for security, bugs, quality
├─ AI finds issues and explains root causes
└─ AI provides fixes

Step 4: Main Development Chat
├─ Apply fixes from Agent Review
└─ Feature is complete and secure
```

### Scenario 2: Quick Bug Fix

```
Step 1: Main Development Chat
├─ Explain the bug
├─ AI fixes it directly
└─ Done

(Optional) Step 2: Agent Review Chat
├─ "Review the fix I just made"
└─ Ensure fix is correct and doesn't introduce new issues
```

### Scenario 3: Complex Feature with Multiple Parts

```
Step 1: Backend Planning Chat
├─ Explain: "I want shopping cart with checkout"
├─ AI asks questions about:
│   - Cart persistence (session/DB)
│   - Checkout flow
│   - Payment integration
│   - Order management
├─ AI creates phased plan:
│   Phase 1: Shopping cart
│   Phase 2: Checkout process
│   Phase 3: Order management
└─ You approve: "Approve Phase 1 first"

Step 2: Main Development Chat
├─ Implement Phase 1 (Shopping Cart)
└─ Test it

Step 3: Agent Review Chat
├─ Review Phase 1
└─ Fix any issues

Step 4: Backend Planning Chat
├─ "Phase 1 done, ready for Phase 2"
├─ AI creates Phase 2 plan
└─ Continue cycle...
```

---

## When to Use Which Chat

### Use **Backend Planning Chat** when:
- ✅ Starting a new feature
- ✅ Requirements are unclear
- ✅ Need architecture decisions
- ✅ Complex feature with multiple parts
- ✅ Want to see a plan before coding
- ✅ Need to understand impact on existing code

### Use **Main Development Chat** when:
- ✅ Quick fixes
- ✅ Simple features (you understand clearly)
- ✅ Modifying existing code
- ✅ Following an approved plan
- ✅ General development work
- ✅ Daily coding tasks

### Use **Agent Review Chat** when:
- ✅ Finished coding a feature
- ✅ Before deploying to production
- ✅ Found a bug and want to understand root cause
- ✅ Periodic security audits
- ✅ Code quality checks
- ✅ Performance optimization

---

## Example: Complete Feature Development

**Feature:** User Registration & Login System

### Phase 1: Planning
**Chat:** Backend Planning
```
You: "I want user registration and login"

AI: "I understand. Priority questions:
1. [CRITICAL] Self-registration or admin-created?
2. [CRITICAL] Email/password or social login?
3. [IMPORTANT] Password reset needed?
4. [IMPORTANT] User roles needed?
5. [NICE-TO-HAVE] Email verification?"

You: "Self-registration, email/password, yes reset, basic roles, no verification"

AI: "Implementation Plan:
- Create users table
- Create login/register pages
- Implement password hashing
- Add session management
- Create password reset flow
Ready to implement?"

You: "Approve"
```

### Phase 2: Implementation
**Chat:** Backend Planning (continues) or Main Development
```
AI: "✅ Starting implementation...
- Created users table
- Created login.php
- Created register.php
- Created Auth helper
- Implemented password reset
✅ Complete!"
```

### Phase 3: Review
**Chat:** Agent Review
```
You: "Review the authentication system I just built"

AI: "🔴 CRITICAL - Missing CSRF Protection
Location: register.php:45
Root Cause: Forms don't validate CSRF tokens
Impact: Vulnerable to CSRF attacks
Fix: [provides fixed code]

🟡 MEDIUM - Password Strength Not Enforced
Location: register.php:67
Root Cause: No password complexity requirements
Impact: Weak passwords allowed
Fix: [provides fixed code]

✅ Fixed all issues"
```

### Phase 4: Apply Fixes
**Chat:** Main Development
```
You: "Apply the fixes from Agent Review"
AI: [Applies all fixes]
```

### Phase 5: Deploy
**Chat:** Main Development
```
You: "Deploy to production"
[Run deploy.bat]
```

---

## Best Practices

### 1. Start with Planning for New Features
- Don't jump straight to coding
- Use Backend Planning Chat first
- Get clear requirements and approval

### 2. Review Before Deploying
- Always review code before production
- Use Agent Review Chat
- Fix critical issues first

### 3. Keep Chats Focused
- Don't mix planning and coding in same chat
- Don't mix review and development
- Each chat has a purpose

### 4. Use Parallel Workflows
- Plan Feature A while reviewing Feature B
- Review Feature A while planning Feature B
- Maximize productivity

### 5. Document Decisions
- Backend Planning Chat creates plans (documentation)
- Agent Review Chat explains issues (learning)
- Main Development Chat implements (execution)

---

## Quick Reference

| Task | Use This Chat | Why |
|------|--------------|-----|
| New feature | Backend Planning | Get plan and approval first |
| Quick bug fix | Main Development | Direct and fast |
| Code review | Agent Review | Security and quality focus |
| Architecture decision | Backend Planning | Understand impact |
| Security audit | Agent Review | Specialized in security |
| Daily coding | Main Development | General development |
| Before deployment | Agent Review | Catch issues before live |
| Complex requirement | Backend Planning | Break it down properly |

---

## Summary

**3 Chats = Complete Development Cycle**

1. **Backend Planning** → Understand & Plan
2. **Main Development** → Build & Implement  
3. **Agent Review** → Review & Secure

**Workflow:**
```
Plan → Build → Review → Deploy
  ↑                        ↓
  └────────────────────────┘
```

This creates a complete, secure, and efficient development process!

