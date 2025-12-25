-- Fix FULLTEXT index for combined search
-- This script adds a single FULLTEXT index covering all columns used in the Search model's MATCH() clause.

-- First, drop the existing separate indexes to avoid duplication and save space (optional but recommended)
-- Note: We check if they exist before dropping, but since this is a migration, we can also just try to add the new one.

-- Add the combined index
-- We use a prefix for description_en to avoid "row size too large" errors if needed, 
-- but for FULLTEXT, usually the whole column is indexed.
ALTER TABLE products ADD FULLTEXT INDEX ft_search_combined (name_en, description_en, short_description_en);
