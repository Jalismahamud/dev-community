# Dev Community Platform — Full Spec + Copilot Prompt

Base setup (Filament, Vue+Inertia, image-webp trait, timezone, API response trait,
activity log, migration discipline, etc.) is already done via the master setup
prompt. This document is domain-specific on top of that.

See `dev-community-erd.mermaid` for the full entity-relationship diagram.

---

## 1. Feature Breakdown (deep, not flat CRUD)

### 1.1 Auth & Developer Profile
- Register/login (email+password). Optional: GitHub OAuth via Socialite (fits a dev
  community naturally, and pre-fills avatar/username).
- Profile fields: avatar (webp), bio, tech stack (multi-select tags), GitHub/LinkedIn
  URL, "currently working on" one-liner status.
- Profile completion indicator (encourages filling bio/stack).

### 1.2 News Feed
- Post = title (optional) + markdown body (code block support) + one or more tech tags.
- **Multi-image upload per post**: gallery of images, reorderable, each converted to
  webp via the base `HasImageUpload` trait; rendered with a lightbox viewer on the feed.
- Feed views: All / Following-only / My-Stack (auto-filtered by the viewer's own tags) / Trending.
- Trending = weighted by (likes + comments) in the last 24h, not just raw likes.
- Infinite scroll, not classic pagination.
- **Live feed update:** when someone posts, an event broadcasts on the public `feed`
  channel; open feeds show a "New posts ↑" pill instead of forcibly reordering the
  user's current view.

### 1.3 Likes (post + comment level)
- Toggle like on posts AND on individual comments.
- Like count updates in real time for everyone currently viewing that post (broadcast
  `PostLiked` / `CommentLiked` with the new count, not the whole post payload).
- Denormalized `likes_count` column on `posts` and `comments` updated atomically
  (increment/decrement), not counted live via `COUNT()` on every page load.

### 1.4 Comments — nested/threaded, not flat
- Adjacency-list model: `comments.parent_id` self-referencing, nullable for top-level.
- Support at least 3 levels of visual nesting in the UI; beyond that, flatten with
  "continue thread" rather than infinite indentation.
- Real-time: posting a comment or reply broadcasts on `private-post.{id}` so everyone
  currently viewing that post's detail sees it appear live.
- Edit comment (mark `is_edited`), soft-delete (show "[deleted]" placeholder so thread
  structure under it doesn't collapse).

### 1.5 Real-time Chat
- 1:1 private chat. Presence channel shows who's online.
- Typing indicator via `whisper()` (no DB write).
- Read receipts (`read_at` on messages).
- Unread badge count updates live in the nav without polling.

### 1.6 Notifications
- Real-time bell: new comment/reply, new like (grouped — "X and 4 others liked your
  post"), new message, new follower.
- Use Laravel's notification system + broadcast channel on `private-user.{id}`.

### 1.7 Follow System
- Follow/unfollow other developers. "Following" feed view depends on this.

### 1.8 Admin/Moderation (Filament)
- Manage users, tech tags, reported posts/comments.
- Simple "Report" action on posts/comments feeding a `reports` table Admin reviews.

---

## 2. Real-time Channel Map (Reverb)

| Channel | Type | Event(s) | Purpose |
|---|---|---|---|
| `feed` | Public | `PostCreated` | live "new post" pill on feed |
| `private-post.{id}` | Private | `CommentPosted`, `PostLiked`, `CommentLiked` | live engagement on a post being viewed |
| `private-conversation.{id}` | Private | `MessageSent`, whisper `typing` | 1:1 chat |
| `presence-online-developers` | Presence | join/leave | online status |
| `private-user.{id}` | Private | `NotificationPushed` | personal notification bell |

---

## 3. UI Direction — Modern, Unique, Not Generic SaaS

Avoid the default blue/purple/white SaaS look. Direction: **dark-first, "terminal
meets warm community"** — techy but not cold.

**Color palette**
- Background (base): `#10141A` (near-black, slightly blue-tinted, not pure black)
- Surface/cards: `#161B22`
- Primary accent: `#F5A623` (warm amber — used for primary buttons, active tab, links)
- Secondary accent: `#0FA3B1` (deep teal — used for tags/badges, secondary actions)
- Like/heart accent: `#FF6B6B` (coral)
- Text primary: `#E6E6E6`
- Text muted: `#8B949E`
- Border/divider: `#232A34`

The amber + teal combo on near-black is the signature — it's warm and cool at once,
distinct from typical indigo/violet dev-tool templates.

**Typography**
- Headings: **Space Grotesk** (geometric, techy, distinctive at large sizes)
- Body: **Inter**
- Code blocks / tech tags / usernames-as-handles: **JetBrains Mono**

**Feel**
- Rounded-but-not-bubbly corners (6–8px), subtle 1px borders instead of heavy shadows,
  amber glow only on hover/focus states (not everywhere), tag chips in teal outline
  style rather than solid fill.

---

## 4. THE PROMPT (paste into Copilot/Claude Code after base setup)

```
Build the domain-specific features for a developer community platform on top of
the existing base setup (Filament + Vue/Inertia + HasImageUpload trait + ApiResponse
trait + ActivityLog + Asia/Dhaka timezone helper + migration discipline rules already
in place — reuse all of these, do not reinvent them).

### Database
Implement the schema exactly as described in dev-community-erd.mermaid:
users (extend with avatar_path, bio, github_url, current_status), tech_tags,
user_tech_tag, posts, post_tag, post_images, post_likes, comments (self-referencing
parent_id, depth, is_edited), comment_likes, follows, conversations,
conversation_participants, messages, notifications (use Laravel's built-in
notifications table), reports.
Add denormalized likes_count and comments_count columns on posts, and likes_count
on comments, maintained via atomic increment/decrement — never via live COUNT().

### Feed
- Post creation: title (optional) + markdown body + multiple tech tags + multiple
  images (use the existing image-upload trait for every image, always converted to
  webp, reorderable via a sort_order column).
- Feed endpoint supports: all / following-only / my-stack / trending, infinite
  scroll (cursor-based pagination, not offset).
- Broadcast a PostCreated event on a public "feed" channel whenever a post is
  created; frontend shows a "New posts" pill rather than force-reordering the
  current scroll position.

### Likes & nested comments
- Toggle-like on both posts and comments; broadcast the updated count only
  (not the full model) on a private-post.{id} channel.
- Comments use adjacency-list nesting (parent_id, self-referencing). Support at
  least 3 visual nesting levels in the Vue component; beyond that collapse into
  a "continue thread" link. Soft-delete comments and render "[deleted]" instead
  of removing the row, so replies under it are preserved.
- Broadcast CommentPosted on the same private-post.{id} channel so open viewers
  see new comments/replies live.

### Chat
- 1:1 conversations. Presence channel for online/offline. Typing indicator via
  Echo's whisper (no DB write for typing state). Track read_at on messages for
  read receipts. Unread counts pushed live via private-user.{id}.

### Notifications
- Use Laravel's notification system (database + broadcast channels) for: new
  comment/reply, new like (grouped as "X and N others liked your post"), new
  message, new follower. Deliver over private-user.{id}.

### Follow system
- Simple follower/following relationship feeding the "Following" feed filter.

### Admin (Filament)
- Resource pages for Users, Tech Tags, and Reports (reported posts/comments)
  with an approve/dismiss/remove action.

### UI theme
Apply this design system globally via Tailwind config:
- Background #10141A, surface #161B22, primary accent #F5A623, secondary accent
  #0FA3B1, like accent #FF6B6B, text #E6E6E6 / muted #8B949E, border #232A34.
- Headings font: Space Grotesk. Body font: Inter. Code/tags/usernames: JetBrains
  Mono. Import all three via Google Fonts or self-hosted.
- Rounded corners 6-8px, thin 1px borders instead of heavy box-shadows, amber
  glow only on hover/focus, teal outline-style tag chips (not solid fill).
- Keep this theme consistent across feed, chat, profile, and Filament admin
  panel branding.
```
