-- "QUESTION" QUERIES --
------------------------

-- Which users watch a particular entity (and therefore need to be notified when it is updated)?
-- In practice '4' would be replaced with the ID of the entity we are interested in.
SELECT u.* FROM users u JOIN user_watched_items uwi ON u.id = uwi.user_id WHERE uwi.watchable_id = 4;

-- Which project(s) does a given user watch?
-- Queries for organizations and contributors are nearly identical.
-- '1' would be replaced with the ID of the user in question.
SELECT p.* FROM projects p JOIN user_watched_items uwi ON p.id = uwi.watchable_id WHERE uwi.user_id = 1;

-- Which organization maintains each project?
SELECT p.name, p.repo, p.short_description, o.name AS maintainer FROM projects p JOIN organizations o ON p.owner_id = o.id;

-- Who are all the developers for Mozilla?
-- Note: If this functionality were included in the app, the join on organizations would
-- probably not be used, since the ID for Mozilla would already be known.
SELECT c.*, om.role FROM contributors c JOIN org_members om ON c.id = om.contributor_id JOIN organizations o on o.id = om.org_id
WHERE o.name = 'Mozilla' AND om.role ILIKE '%developer%';

-- Get the details about all contributors, along with whether the current user watches each contributor
-- Note: In practice the user ID is the one of the logged-in user. This query is actually used in the application;
-- in practice it is sometimes constrained to return just one contributor.
SELECT c.*, NOT(uwi.user_id IS NULL) AS watched FROM contributors c
    LEFT JOIN (SELECT * FROM user_watched_items WHERE user_id = 1) uwi
        ON c.id = uwi.watchable_id;
        
-- Get all licenses that apply to a project, with links to their full text
-- Actually used in the application
-- '1' would be replaced with the ID of the project in question
SELECT * FROM licenses l JOIN project_licenses pl ON l.id = pl.license_id JOIN websites w on l.text_link = w.id WHERE pl.project_id = 1;

-- Find organizations maintaining projects that have no contributors
-- as well as the project names
-- Maybe these organizations are good places to start looking for a job!
SELECT o.*, projects.name AS project_name FROM organizations o JOIN
    (SELECT name, owner_id FROM projects
        WHERE NOT EXISTS (SELECT project_id FROM project_contributors pc WHERE pc.project_id = id)) projects
    ON o.id = projects.owner_id;
    
-- What projects does a given organization maintain?
-- 'Organization' would be replaced with the name of the desired organization.
SELECT p.* FROM projects p JOIN organizations o ON p.owner_id = o.id WHERE o.name='Organization';

-- Given two projects, what contributors do they have in common?
-- '1' and '2' would be replaced with the IDs of the relevant projects.
SELECT c.* FROM contributors c JOIN (
    (SELECT contributor_id FROM project_contributors WHERE project_id = 1)
    INTERSECT
    (SELECT contributor_id FROM project_contributors WHERE project_id = 2)) pc
    ON c.id = pc.contributor_id;
    
-- Are there any contributors with the same name as me?
-- '1' would be replaced with the ID of the logged in user.
SELECT c.* FROM contributors c JOIN users u ON c.name ILIKE ('%' || u.name || '%') WHERE u.id = 1;

-- "REPORT" QUERIES --
----------------------

-- How many projects does each organization maintain?
SELECT o.id, o.name, o.short_description, COUNT(p) AS num_projects FROM organizations o LEFT JOIN projects p ON o.id = p.owner_id GROUP BY o.id;

-- What are the 10 most popular entities to watch?
-- Getting the entity names is difficult because they may be stored in one of three tables.
-- Our database was designed to work for use cases where (1) the type of entity desired is already known,
-- or (2) the type of entity isn't relevant.
-- These are the only use cases in our application; if other use cases were added, different design
-- decisions would be required for the database.
SELECT w.id, COALESCE(p.name, o.name, c.name) AS name, COUNT(uwi) AS num_watchers
FROM watchables w LEFT JOIN projects p ON w.id = p.id
LEFT JOIN organizations o ON w.id = o.id
LEFT JOIN contributors c ON w.id = c.id
LEFT JOIN user_watched_items uwi ON w.id = uwi.watchable_id
GROUP BY w.id, p.name, o.name, c.name ORDER BY num_watchers DESC LIMIT 10;

-- Which contributor(s) contribute to the most projects?
SELECT c.*, COUNT(pc) AS num_projects FROM contributors c JOIN project_contributors pc ON c.id = pc.contributor_id GROUP BY c.id
HAVING COUNT(pc) = (SELECT MAX(num_projects) FROM (SELECT COUNT(*) AS num_projects FROM project_contributors GROUP BY contributor_id) a);

-- Function to determine whether a URI uses HTTPS
CREATE OR REPLACE FUNCTION USES_HTTPS (uri VARCHAR)
RETURNS boolean
AS $$
BEGIN
    return position('https://' in uri) = 1;
END;
$$ language plpgsql;

-- How many of our websites' URIs are secure/not secure?
-- Although this query returns only 1-2 rows, it is a "report" in that it summarizes
-- information about all the websites in the database (and most entities have at least
-- one website).
SELECT COUNT(*), (CASE WHEN USES_HTTPS(uri) THEN 'secure' ELSE 'insecure' END) AS secure FROM websites GROUP BY USES_HTTPS(uri);

-- Function for getting the domain name out of a URI
CREATE OR REPLACE FUNCTION GET_DOMAIN (uri VARCHAR)
RETURNS VARCHAR
AS $$
DECLARE
    WS_DOMAIN VARCHAR;
    END_DOMAIN INT;
BEGIN
    WS_DOMAIN := uri;
    -- trim off any protocol (http:// etc)
    WS_DOMAIN := ltrim(substr(WS_DOMAIN, position('//' in WS_DOMAIN)), '/');
    -- trim off any URI (after the first remaining '/')
    END_DOMAIN := position('/' in WS_DOMAIN);
    if END_DOMAIN > 0 then
        WS_DOMAIN := substr(WS_DOMAIN, 1, END_DOMAIN - 1);
    end if;
    return WS_DOMAIN;
END;
$$ LANGUAGE plpgsql;

-- How many organizations are hosted on each DNS domain?
SELECT GET_DOMAIN(homepage) AS domain_name, COUNT(*) AS num_organizations FROM organizations
GROUP BY domain_name ORDER BY num_organizations DESC;