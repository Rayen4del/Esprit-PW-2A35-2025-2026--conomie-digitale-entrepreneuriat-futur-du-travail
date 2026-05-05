-- ============================================================
-- DATABASE MIGRATION: Add Soft Delete Support
-- Date: May 5, 2026
-- ============================================================
-- This migration adds soft delete support to posts and comments
-- by adding a 'deleted_at' column to track when records are deleted

-- Add deleted_at column to post table
ALTER TABLE post 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER DatePublication;

-- Add deleted_at column to commentaire table
ALTER TABLE commentaire 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER DateCom;

-- ============================================================
-- Notes:
-- - deleted_at will be NULL for active records
-- - When a record is deleted (soft delete), deleted_at will be set to NOW()
-- - When a record is restored, deleted_at will be set back to NULL
-- - Queries should filter out deleted records with: WHERE deleted_at IS NULL
-- ============================================================
