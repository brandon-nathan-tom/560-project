DROP TABLE IF EXISTS websites;
DROP TABLE IF EXISTS watchables;
DROP TABLE IF EXISTS watchables_sites;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS contributors;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS download_mirrors;
DROP TABLE IF EXISTS org_members;
DROP TABLE IF EXISTS project_contributors;
DROP TABLE IF EXISTS licenses;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS user_watched_items;

CREATE TABLE websites (
	id	int NOT NULL,
	name	varchar NOT NULL,
	uri	varchar NOT NULL,
	descr	varchar,
	PRIMARY KEY(ID, name, uri),
	UNIQUE (uri)
);

CREATE TABLE watchables (
	id	int NOT NULL,
	PRIMARY KEY(ID)
);

CREATE TABLE watchables_sites (
	watchable_id	int NOT NULL REFERENCES(watchables.id),
	site_id		int NOT NULL REFERENCES(websites.id)
);

CREATE TABLE organizations (
	id	int NOT NULL REFERENCES(watchables.id),
	name	varchar NOT NULL,
	email	varchar NOT NULL
);

CREATE TABLE contributors (
	id	int NOT NULL REFERENCES(watchables.id),
	name	varchar NOT NULL,
	email	varchar NOT NULL
);

CREATE TABLE projects (
	id		int NOT NULL REFERENCES(watchables.id),
	name		varchar NOT NULL,
	repo		varchar NOT NULL,
	owner_id	int NOT NULL REFERENCES(organizations.id)
);

CREATE TABLE download_mirrors (
	project_id	int NOT NULL REFERENCES(projects.id),
	site_id		int NOT NULL REFERENCES(websites.id)
);

CREATE TABLE org_members (
	org_id		int NOT NULL REFERENCES(organizations.id),
	contributor_id	int NOT NULL REFERENCES(contributors.id),
	role		varchar
);

CREATE TABLE project_contributors (
	project_id	int NOT NULL REFERENCES(projects.id),
	contributor_id	int NOT NULL REFERENCES(contributors.id),
	role		varchar
);

CREATE TABLE licenses (
	id		int NOT NULL,
	name		varchar NOT NULL,
	text_link	varchar NOT NULL REFERENCES(websites.id),
	UNIQUE(name, text_link),
	PRIMARY KEY(id)
);

CREATE TABLE users (
	id		int NOT NULL,
	nickname	varchar NOT NULL,
	name		varchar,
	email		varchar NOT NULL,
	pw_hash		varchar NOT NULL,
	UNIQUE(nickname),
	PRIMARY KEY(id, email)
);

CREATE TABLE user_watched_items (
	user_id		int NOT NULL REFERENCES(users.id),
	watchable_id	int NOT NULL REFERENCES(watchables.id)
);
