-- Create testing database for RMS
-- Run this with: psql -U postgres -f setup-test-db.sql

-- Drop if exists (optional, uncomment if needed)
-- DROP DATABASE IF EXISTS rms_testing;

-- Create the testing database
CREATE DATABASE rms_testing;

-- Grant privileges (adjust username if needed)
GRANT ALL PRIVILEGES ON DATABASE rms_testing TO postgres;
