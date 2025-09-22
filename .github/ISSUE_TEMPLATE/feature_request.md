---
name: Feature request
about: Suggest an idea for this project
title: ''
labels: ''
assignees: ''

---

**Is your feature request related to a problem? Please describe.**
A clear and concise description of what the problem is. Ex. I'm always frustrated when [...]

#### **Description**

When a Hunter follows another, the followed Hunter should receive a notification indicating who followed them. This helps users stay connected and aware of new followers.

---

#### **Expected Behavior**

- When a Hunter clicks "Follow", a notification is triggered for the followed Hunter.
- The notification includes:
  - Username of the follower
  - A link to their profile
  - A timestamp
- The notification appears in the recipient's notification center (or UI area).
- Notifications are marked as unread until opened or viewed.

---

#### **Frontend Tasks**

- Add logic to trigger and display follow notifications in the UI.
- Optionally show a toast or badge to indicate new activity.

---

#### **Backend Tasks**

- Create notification entry in the database when a follow action is performed.
- Include relevant metadata (follower ID, timestamp, read status).
- Optional: Send email notification or push (if enabled).

---

#### **Optional Enhancements**

- Add notification settings for Hunters to manage what types of alerts they receive.
- Real-time updates using WebSockets or polling.
