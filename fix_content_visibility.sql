-- Fix content items to make them visible in API
-- Run this SQL to update your existing content

-- Update all content items to be public and published
UPDATE content_items 
SET 
    visibility = 'public',
    published_at = NOW()
WHERE 
    visibility IS NULL 
    OR published_at IS NULL;

-- Verify the changes
SELECT 
    id, 
    title, 
    type,
    visibility, 
    published_at,
    created_at
FROM content_items
LIMIT 10;
