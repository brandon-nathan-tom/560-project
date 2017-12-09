DROP TABLE IF EXISTS websites CASCADE;
DROP TABLE IF EXISTS watchables CASCADE;
DROP TABLE IF EXISTS watchables_sites CASCADE;
DROP TABLE IF EXISTS organizations CASCADE;
DROP TABLE IF EXISTS contributors CASCADE;
DROP TABLE IF EXISTS projects CASCADE;
DROP TABLE IF EXISTS download_mirrors CASCADE;
DROP TABLE IF EXISTS org_members CASCADE;
DROP TABLE IF EXISTS project_contributors CASCADE;
DROP TABLE IF EXISTS licenses CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS user_watched_items CASCADE;

CREATE TABLE websites (
	id	int NOT NULL,
	name	varchar(40) NOT NULL,
	uri	varchar(250) NOT NULL,
	descr	varchar(400),
	PRIMARY KEY(id),
	UNIQUE (uri)
);

CREATE TABLE watchables (
	id	int NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE watchables_sites (
	watchable_id	int NOT NULL REFERENCES watchables(id),
	site_id		int NOT NULL REFERENCES websites(id)
);

CREATE TABLE organizations (
	id	int NOT NULL REFERENCES watchables(id),
	name	varchar(40) NOT NULL,
	email	varchar(40) NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE contributors (
	id	int NOT NULL REFERENCES watchables(id),
	name	varchar(40) NOT NULL,
	email	varchar(40) NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE projects (
	id		int NOT NULL REFERENCES watchables(id),
	name		varchar(40) NOT NULL,
	repo		varchar(2000) NOT NULL,
	owner_id	int NOT NULL REFERENCES organizations(id),
	PRIMARY KEY(id)
);

CREATE TABLE download_mirrors (
	project_id	int NOT NULL REFERENCES projects(id),
	site_id		int NOT NULL REFERENCES websites(id),
	PRIMARY KEY(project_id, site_id)
);

CREATE TABLE org_members (
	org_id		int NOT NULL REFERENCES organizations(id),
	contributor_id	int NOT NULL REFERENCES contributors(id),
	role		varchar(40),
	PRIMARY KEY(org_id, contributor_id)
);

CREATE TABLE project_contributors (
	project_id	int NOT NULL REFERENCES projects(id),
	contributor_id	int NOT NULL REFERENCES contributors(id),
	role		varchar(40),
	PRIMARY KEY(project_id, contributor_id)
);

CREATE TABLE licenses (
	id		int NOT NULL,
	name		varchar(40) NOT NULL,
	text_link	int NOT NULL REFERENCES websites(id),
	UNIQUE(name, text_link),
	PRIMARY KEY(id)
);

CREATE TABLE users (
	id		int NOT NULL,
	nickname	varchar(40) NOT NULL,
	name		varchar(40),
	email		varchar(40) NOT NULL,
	pw_hash		varchar(255) NOT NULL,
	UNIQUE(nickname),
	PRIMARY KEY(id)
);

CREATE TABLE user_watched_items (
	user_id		int NOT NULL REFERENCES users(id),
	watchable_id	int NOT NULL REFERENCES watchables(id)
);
