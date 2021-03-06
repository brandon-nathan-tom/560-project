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
DROP TABLE IF EXISTS project_licenses CASCADE;

CREATE TABLE websites (
	id	 serial NOT NULL,
	name	varchar(40) NOT NULL,
	uri	varchar(250) NOT NULL,
	descr	varchar(400),
	PRIMARY KEY(id),
	UNIQUE (uri)
);

CREATE TABLE watchables (
	id	serial NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE watchables_sites (
	watchable_id	int NOT NULL REFERENCES watchables(id),
	site_id		int NOT NULL REFERENCES websites(id)
);

CREATE TABLE organizations (
	id	int NOT NULL REFERENCES watchables(id),
	name	varchar(40) NOT NULL,
	homepage	varchar(40) NOT NULL,
	short_description varchar(40) NOT NULL,
	description varchar(2000) NOT NULL,
	PRIMARY KEY(id)
);

CREATE TABLE contributors (
	id	int NOT NULL REFERENCES watchables(id),
	name	varchar(40) NOT NULL,
	email	varchar(40) NOT NULL,
	PRIMARY KEY(id),
	UNIQUE(name, email)
);

CREATE TABLE licenses (
	id		serial NOT NULL,
	name		varchar(40) NOT NULL,
	text_link	int NOT NULL REFERENCES websites(id),
	UNIQUE(name),
	PRIMARY KEY(id)
);

CREATE TABLE projects (
	id		int NOT NULL REFERENCES watchables(id),
	name		varchar(40) NOT NULL,
	repo		varchar(2000) NOT NULL,
	owner_id	int NOT NULL REFERENCES organizations(id),
	short_description varchar(40) NOT NULL,
	description varchar(2000) NOT NULL,
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

CREATE TABLE users (
	id		serial NOT NULL,
	nickname	varchar(40) NOT NULL,
	name		varchar(40),
	email		varchar(40) NOT NULL,
	pw_hash		varchar(255) NOT NULL,
	UNIQUE(nickname),
	PRIMARY KEY(id)
);

CREATE TABLE user_watched_items (
	user_id		int NOT NULL REFERENCES users(id),
	watchable_id	int NOT NULL REFERENCES watchables(id),
	PRIMARY KEY(user_id, watchable_id)
);

create table project_licenses (
	project_id int not null references projects(id),
	license_id int not null references licenses(id),
	PRIMARY KEY(project_id, license_id)
);
