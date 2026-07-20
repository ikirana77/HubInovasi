USE hubinovasi;

-- CP10F reuses the CP10A media, links and milestones tables. Only the cover
-- marker required by the gallery is added here.
SET @cp10f_cover_column := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'submission_media'
      AND COLUMN_NAME = 'is_cover'
);
SET @cp10f_sql := IF(
    @cp10f_cover_column = 0,
    'ALTER TABLE submission_media ADD COLUMN is_cover TINYINT(1) NOT NULL DEFAULT 0 AFTER caption',
    'SELECT 1'
);
PREPARE cp10f_statement FROM @cp10f_sql;
EXECUTE cp10f_statement;
DEALLOCATE PREPARE cp10f_statement;

-- Early CP10F builds used project_gallery. Normalise those rows to the
-- canonical CP10A media type without touching media owned by other sections.
UPDATE submission_media
SET media_type = 'gallery'
WHERE media_type = 'project_gallery';

SET @cp10f_cover_index := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'submission_media'
      AND INDEX_NAME = 'idx_submission_media_cover'
);
SET @cp10f_sql := IF(
    @cp10f_cover_index = 0,
    'ALTER TABLE submission_media ADD INDEX idx_submission_media_cover (submission_id, media_type, is_cover, sort_order)',
    'SELECT 1'
);
PREPARE cp10f_statement FROM @cp10f_sql;
EXECUTE cp10f_statement;
DEALLOCATE PREPARE cp10f_statement;
