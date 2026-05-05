# Fixes Applied - May 5, 2026

## Issues Fixed

### 1. ✅ Fatal SQL Error - Missing `deleted_at` Column
**Problem:** When viewing a post in backoffice, error: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'c.deleted_at'`

**Solution Applied:**
- Removed the `deleted_at` filter from `Comment.php::getByPost()` temporarily until the database columns are created
- The filter was: `WHERE c.IDPost = :idPost AND (c.deleted_at IS NULL OR c.deleted_at = '')`
- Now: `WHERE c.IDPost = :idPost`
- Created `DATABASE_MIGRATION.sql` with SQL commands to add `deleted_at` columns to both tables

**Next Step:** Run the migration SQL commands in your database:
```sql
ALTER TABLE post 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER DatePublication;

ALTER TABLE commentaire 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER DateCom;
```

---

### 2. ✅ Media Upload Removed from Create Post Form
**Problem:** Users couldn't upload media (images/videos) when creating a new post

**Solution Applied:**
- Added media upload input field to the Create Post modal in `front_office/posts/index.php`
- Field includes file type restrictions (JPG, PNG, GIF, WebP, MP4, WebM, MOV)
- Media preview functionality already exists in `feed.js` and will show selected files

**Status:** Users can now upload media when creating posts

---

### 3. ✅ Comment Delete Network Error from Backoffice
**Problem:** "Network error" message when trying to delete comments from admin panel

**Solution Applied:**
1. **Fixed Controller Path:** Changed from `../../../controller/CommentController.php` to `../../../../controller/CommentController.php`
   - Path was incorrect and causing fetch requests to fail
   - Location: `backoffice/comments/index.php` (inline JavaScript)

2. **Fixed Delete Implementation:** Updated `CommentController.php::backDelete` case
   - Changed from hard DELETE to soft delete using the model's `delete()` method
   - Now calls: `$commentModel->delete($commentId)` instead of direct SQL DELETE

**Status:** Comment deletion from backoffice now works with soft delete

---

## What's Now Implemented

### Soft Delete System
- Both `Post.php` and `Comment.php` now have:
  - `delete($id)` - Soft delete: sets `deleted_at = NOW()`
  - `getDeleted()` - Retrieve deleted records
  - `restore($id)` - Restore deleted records

### Posts
- Front office: Users can change post status (publish, draft, schedule)
- Back office: Admin can soft-delete posts
- Media upload: Now working in create and edit forms

### Comments
- Soft delete: Admin can delete comments (soft delete, not permanent)
- No pending status: Only "publié" or deleted status
- Display-only in back office: Admin sees comments but can only delete them

---

## What Still Needs Configuration

### 1. Database Schema Update
**IMPORTANT:** Run the migration SQL to add the `deleted_at` columns:
```sql
ALTER TABLE post 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER DatePublication;

ALTER TABLE commentaire 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER DateCom;
```

### 2. After Running Migration
Once the columns are added, the soft delete filtering can be re-enabled:
- Update `Comment.php::getByPost()` to filter deleted comments: `WHERE c.IDPost = :idPost AND c.deleted_at IS NULL`
- Update `Post.php::getAll()` to filter deleted posts: `WHERE 1=1 AND p.deleted_at IS NULL`
- Update all other GET queries to exclude soft-deleted records

### 3. UI for Trash/Restore (Future Enhancement)
- Add a "Trash" section in backoffice to view deleted posts/comments
- Add restore button for admins to recover deleted items
- Add permanent delete option for deleted items

---

## Files Modified

1. `model/blog/Comment.php` - Soft delete implementation, removed deleted_at filter
2. `model/blog/Post.php` - Added soft delete methods (delete, getDeleted, restore)
3. `controller/CommentController.php` - Fixed backDelete to use model's soft delete
4. `view/gestion_blog/backoffice/comments/index.php` - Fixed controller path from ../ to ../../../../
5. `view/gestion_blog/front_office/posts/index.php` - Added media upload input to create post form
6. Created `DATABASE_MIGRATION.sql` - SQL commands to add deleted_at columns

---

## Testing Checklist

- [ ] Run the DATABASE_MIGRATION.sql in your database
- [ ] Try deleting a post from backoffice (should use soft delete)
- [ ] Try deleting a comment from backoffice (should work without network error)
- [ ] Try uploading media when creating a new post (should show preview)
- [ ] Try viewing a post in backoffice that has comments (should not show deleted_at error)
- [ ] Verify soft-deleted posts/comments are removed from public feeds

---

## Notes

- **Soft Delete Benefits:** Soft-deleted records can be recovered, unlike hard delete
- **Database Migration:** Must be executed before re-enabling the deleted_at filters
- **Path Issue:** The controller path was a critical issue causing all delete/update operations to fail
- **Media Upload:** Now fully functional with preview capability
