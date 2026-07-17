USE hubinovasi;
ALTER TABLE submissions
    ADD COLUMN IF NOT EXISTS project_development_status VARCHAR(120) NULL AFTER solution_area;
