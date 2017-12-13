#include "basic_auth.h"

#include <jansson.h>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <curl/curl.h>

#define BUFFER_SIZE  (256 * 1024)  /* 256 KB */
#define URL_FORMAT "https://api.github.com/repos/%s/%s/commits"
#define URL_ORG "https://api.github.com/orgs/%s"
#define URL_ORG_MEMBERS "https://api.github.com/orgs/%s/members"
#define URL_SIZE 256

#define SSL_OPTIONS CURLSSLOPT_NO_REVOKE
#define SQL_TEXT(txt) txt "\n"
#define check_or_complain(check, complaint, arg1, arg2)	\
  if(check){											\
	fprintf(stderr, complaint, arg1, arg2);				\
	json_decref(request_root);									\
	return 1;											\
  }


#define MACRO_json_get_string(thing, var, field)	\
  var = json_object_get(thing,						\
						field);						\
  if(json_is_null(var)){							\
	string_ ## var = NULL;										\
  } else{											\
	check_or_complain(!json_is_string(var),			\
					  field " is not a string\n",	\
					  NULL, NULL);					\
	string_ ## var = json_string_value(var);		\
  }

#define MACRO_pretty_print_string(var)			\
  if(var){										\
	printf(#var":%s\n", string_ ## var);		\
  }else{										\
	printf(#var":null\n");						\
  }

static int
newline_offset(const char *text){
  const char *newline = strchr(text, '\n');
  if(!newline){
	return strlen(text);
  } else {
	return (int)(newline - text);
  }
}
struct write_result
{
    char *data;
    int pos;
};

static size_t write_response(void *ptr, size_t size, size_t nmemb, void *stream)
{
    struct write_result *result = (struct write_result *)stream;

    if(result->pos + size * nmemb >= BUFFER_SIZE - 1)
    {
        fprintf(stderr, "error: too small buffer\n");
        return 0;
    }

    memcpy(result->data + result->pos, ptr, size * nmemb);
    result->pos += size * nmemb;

    return size * nmemb;
}

static char *request(const char *url)
{
    CURL *curl = NULL;
    CURLcode status;
    struct curl_slist *headers = NULL;
    char *data = NULL;
    long code;

	/* printf("requesting url:'%s'\n", url); */

    curl_global_init(CURL_GLOBAL_ALL);
    curl = curl_easy_init();
    if(!curl)
        goto error;

    data = malloc(BUFFER_SIZE);
    if(!data)
        goto error;

    struct write_result write_result = {
        .data = data,
        .pos = 0
    };

    curl_easy_setopt(curl, CURLOPT_URL, url);
	curl_easy_setopt(curl, CURLOPT_SSL_VERIFYPEER, 0);

    /* GitHub commits API v3 requires a User-Agent header */
    headers = curl_slist_append(headers, "User-Agent: niebieskitrociny");
	
    curl_easy_setopt(curl, CURLOPT_HTTPAUTH, (long)CURLAUTH_BASIC);
    curl_easy_setopt(curl, CURLOPT_USERNAME, BASIC_AUTH_USER);
	curl_easy_setopt(curl, CURLOPT_PASSWORD, BASIC_AUTH_PASS);
	
    curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);

    curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, write_response);
    curl_easy_setopt(curl, CURLOPT_WRITEDATA, &write_result);

    status = curl_easy_perform(curl);
    if(status != 0)
    {
        fprintf(stderr, "error: unable to request data from %s:\n", url);
        fprintf(stderr, "%s\n", curl_easy_strerror(status));
        goto error;
    }

    curl_easy_getinfo(curl, CURLINFO_RESPONSE_CODE, &code);
    if(code != 200)
    {
        fprintf(stderr, "error: server responded with code %ld\n", code);
        goto error;
    }

    curl_easy_cleanup(curl);
    curl_slist_free_all(headers);
    curl_global_cleanup();

    /* zero-terminate the result */
    data[write_result.pos] = '\0';

    return data;

error:
    if(data)
        free(data);
    if(curl)
        curl_easy_cleanup(curl);
    if(headers)
        curl_slist_free_all(headers);
    curl_global_cleanup();
    return NULL;
}

int
handle_commits(int argc, char **argv){
  size_t i;
  char *text;
  char url[URL_SIZE];
  json_t *request_root;
  json_error_t error;
  
  snprintf(url, URL_SIZE, URL_FORMAT, argv[1], argv[2]);

  text = request(url);
  if(!text){
	return 1;
  }

  request_root = json_loads(text, 0, &error);
  free(text);

  check_or_complain(!request_root, "error: on line %d: %s\n", error.line, error.text);

  check_or_complain(!json_is_array(request_root), "error: root is not an array.\n\n", NULL, NULL);

  for(i=0;
	  i<json_array_size(request_root);
	  i++){
	json_t *data, *sha, *commit, *message;
	const char *message_text;

	data = json_array_get(request_root, i);
	check_or_complain(!json_is_object(data), "error: commit data %d is not an object.\n\n", i+1, NULL);

	sha = json_object_get(data, "sha");
	check_or_complain(!json_is_string(sha), "error: commit %d: sha is not a string.\n\n", i+1, NULL);

	commit = json_object_get(data, "commit");
	check_or_complain(!json_is_object(commit), "error: commit %d: commit is not an object.\n\n", i+1, NULL);

	message = json_object_get(commit, "message");
	check_or_complain(!json_is_string(message), "error: commit %d: message is not a string.\n\n", i+1, NULL);

	message_text = json_string_value(message);
	printf("%.8s %.*s\n",
		   json_string_value(sha),
		   newline_offset(message_text),
		   message_text);
  }

  json_decref(request_root);
  return 1;
}

#define MACRO_json_request(url)								\
  json_t *request_root;										\
  json_error_t json_error;									\
  char *request_text;										\
  request_text = request(url);								\
  if(!request_text){										\
	printf("bad request.\n");								\
	return 1;												\
  }															\
  request_root = json_loads(request_text, 0, &json_error);	\
  check_or_complain(!request_root, /* check not null */		\
					"error on line %d, %s\n",				\
					json_error.line,						\
					json_error.text);
#define MACRO_print_request()					\
  printf("%s\n", request_text);
#define MACRO_clean_request()\
  free(request_text);
#define MACRO_silent_request(url)\
  MACRO_json_request(url);\
  MACRO_clean_request();
#define MACRO_verbose_request(url)\
  MACRO_json_request(url);\
  MACRO_print_request();\
  MACRO_clean_request();

#define MACRO_json_string(var)					\
  const char *string_ ## var;					\
  json_t *var;

int
handle_user_org(const char *user_url, const char *org_name){
  MACRO_json_string(email);
  MACRO_json_string(name);
  /* MACRO_json_string(nickname); */
  
  /* printf("url:'%s'\n", user_url); */
  MACRO_silent_request(user_url);
  check_or_complain(!json_is_object(request_root),
					"root is not an object\n",
					NULL, NULL);
  
  MACRO_json_get_string(request_root, email, "email");
  MACRO_json_get_string(request_root, name, "name");
  /* MACRO_json_get_string(request_root, nickname, "login"); */

  char statement[] =
	SQL_TEXT("with watch_contrib as (")
	SQL_TEXT("    insert into watchables")
	SQL_TEXT("    default values")
	SQL_TEXT("    returning id")
	SQL_TEXT(")")
	SQL_TEXT("insert into contributors (id, name, email)")
	SQL_TEXT("select watch_contrib.id, '%.40s', '%.40s'")
	SQL_TEXT("from watch_contrib")
	SQL_TEXT("returning id;")
	/* associate with org */
	SQL_TEXT("\nwith org_select as (")
	SQL_TEXT("select * from organizations")
	SQL_TEXT("where name = '%s'")
	SQL_TEXT("), contrib_select as (")
	SQL_TEXT("select * from contributors")
	SQL_TEXT("where name = '%s'")
	SQL_TEXT(")")
	SQL_TEXT("insert into org_members (org_id, contributor_id, role)")
	SQL_TEXT("select org_select.id, contrib_select.id, 'User'")
	SQL_TEXT("from org_select, contrib_select;");

  if(string_name == NULL){
	// no point
	return 1;
  }
  
  if(string_email == NULL){
	string_email = malloc(41 * sizeof(char));
	snprintf(string_email, 41, "<private>");
  }
  
  char *buffer = malloc(10000 * sizeof(char));
  snprintf(buffer,
		   10000,
		   statement,
		   string_name,
		   string_email,
		   org_name,
		   string_name);
  printf("%s\n", buffer);
  free(buffer);
  /* printf("User data collected:\n"); */
  /* MACRO_pretty_print_string(name); */
  /* MACRO_pretty_print_string(email); */
  /* MACRO_pretty_print_string(nickname); */
  return 0;
}

int
handle_user_proj(const char *user_url, const char *proj_name){
  MACRO_json_string(email);
  MACRO_json_string(name);
  /* MACRO_json_string(nickname); */
  
  /* printf("url:'%s'\n", user_url); */
  MACRO_silent_request(user_url);
  check_or_complain(!json_is_object(request_root),
					"root is not an object\n",
					NULL, NULL);
  
  MACRO_json_get_string(request_root, email, "email");
  MACRO_json_get_string(request_root, name, "name");
  /* MACRO_json_get_string(request_root, nickname, "login"); */

  char statement[] =
	SQL_TEXT("with watch_contrib as (")
	SQL_TEXT("    insert into watchables")
	SQL_TEXT("    default values")
	SQL_TEXT("    returning id")
	SQL_TEXT(")")
	SQL_TEXT("insert into contributors (id, name, email)")
	SQL_TEXT("select watch_contrib.id, '%.40s', '%.40s'")
	SQL_TEXT("from watch_contrib")
	SQL_TEXT("returning id;")
	/* associate with proj */
	SQL_TEXT("\nwith proj_select as (")
	SQL_TEXT("select * from projects")
	SQL_TEXT("where name = '%s'")
	SQL_TEXT("), contrib_select as (")
	SQL_TEXT("select * from contributors")
	SQL_TEXT("where name = '%s'")
	SQL_TEXT(")")
	SQL_TEXT("insert into project_contributors (project_id, contributor_id, role)")
	SQL_TEXT("select proj_select.id, contrib_select.id, 'User'")
	SQL_TEXT("from proj_select, contrib_select;");

  if(string_email == NULL){
	string_email = malloc(41 * sizeof(char));
	snprintf(string_email, 41, "<private>");
  }
  
  if(string_name == NULL){
	// no point
	return 1;
  }
  
  char *buffer = malloc(10000 * sizeof(char));
  snprintf(buffer,
		   10000,
		   statement,
		   string_name,
		   string_email,
		   proj_name,
		   string_name);
  printf("%s\n", buffer);
  free(buffer);
  /* printf("User data collected:\n"); */
  /* MACRO_pretty_print_string(name); */
  /* MACRO_pretty_print_string(email); */
  /* MACRO_pretty_print_string(nickname); */
  return 0;
}

int
handle_members(const char *members_url, const char *assoc_name, int (*fun_ptr)(const char *, const char *)){
  MACRO_silent_request(members_url);
  /* MACRO_print_request(); */
  check_or_complain(!json_is_array(request_root),
					"root is not an array\n",
					NULL, NULL);
  for(int i=0;
	  i<json_array_size(request_root);
	  i++){
	json_t *data;
	json_t *login;
	const char *string_login;
	json_t *user_url;
	const char *string_user_url;
	data = json_array_get(request_root, i);
	check_or_complain(!json_is_object(data),
					  "user data %i not an object\n",
					  i, NULL);
	MACRO_json_get_string(data, login, "login");
	MACRO_json_get_string(data, user_url, "url");
	fun_ptr(string_user_url, assoc_name);
  }
  return 0;
}

int
handle_license(const char *lic_uri, char **lic_name, char **lic_html_url, char **lic_descr){
  MACRO_silent_request(lic_uri);
  check_or_complain(!json_is_object(request_root),
					"lic obj not an obj\n",
					NULL, NULL);

  MACRO_json_string(name);
  MACRO_json_string(html_uri);
  MACRO_json_string(description);

  MACRO_json_get_string(request_root, name, "name");
  MACRO_json_get_string(request_root, html_uri, "html_url");
  MACRO_json_get_string(request_root, description, "description");

  *lic_name = string_name;
  *lic_html_url = string_html_uri;
  *lic_descr = string_description;
  return 0;
}

char *
clean_value(char *input, int max) {
  char *temp = malloc(2 * max * sizeof(char));
  memset(temp, 0, 2 * max * sizeof(char));
  int temp_i = 0;
  for(int i=0;
	  i<max;
	  i++){
	if(input[i] == '\''){
	  temp[temp_i] = '\'';
	  temp_i++;
	}
	
	temp[temp_i]=input[i];
	temp_i++;
  }
  return temp;
}

int
handle_repos(const char *repos_url, const char *org_name){
  MACRO_silent_request(repos_url);

  check_or_complain(!json_is_array(request_root),
					"root is not an array\n",
					NULL, NULL);

  for(int i=0;
	  i<json_array_size(request_root);
	  i++){
	json_t *data;
	data = json_array_get(request_root, i);
	check_or_complain(!json_is_object(data),
					  "repo data %i not an object\n",
					  i, NULL);
		
	MACRO_json_string(name);
	MACRO_json_string(repo);
	MACRO_json_string(short_description);
	MACRO_json_string(description);
	MACRO_json_string(contributors);
	MACRO_json_string(homepage);
	MACRO_json_string(html_url);
	MACRO_json_string(releases_url);
	char *releases = malloc(sizeof(char) * 10000);
  
	MACRO_json_get_string(data, name, "name");
	MACRO_json_get_string(data, repo, "git_url");
	MACRO_json_get_string(data, description, "description");
	MACRO_json_get_string(data, contributors, "contributors_url");
	MACRO_json_get_string(data, homepage, "homepage");
	MACRO_json_get_string(data, html_url, "html_url");
	if(json_is_null(homepage)){
	  homepage = html_url;
	  string_homepage = string_html_url;
	}else if(string_homepage == 0 || strlen(string_homepage) == 0){
	  homepage = html_url;
	  string_homepage = string_html_url;
	}
	if(!json_is_null(html_url)){
	  strncpy(releases, string_html_url, 10000);
	  strcat(releases, "/releases");
	}

	json_t *license = json_object_get(data, "license");
	int has_license;
	char *lic_name, *lic_html_url, *lic_descr;
	MACRO_json_string(lic_uri);
	if(!json_is_null(license)){
	  has_license = 1;
	  check_or_complain(!json_is_object(license),
						"license is not an object\n",
						NULL, NULL);

	  MACRO_json_get_string(license, lic_uri, "url");
	  if(json_is_null(lic_uri)){
		has_license = 0;
	  }else {
		handle_license(string_lic_uri,
					   &lic_name,
					   &lic_html_url,
					   &lic_descr);
	  }
	}

	char statement[] =
	  SQL_TEXT("with watch_proj as (")
	  SQL_TEXT("    insert into watchables")
	  SQL_TEXT("    default values")
	  SQL_TEXT("    returning id")
	  SQL_TEXT("), org_select as (")
	  SQL_TEXT("    select *")
	  SQL_TEXT("    from organizations")
	  SQL_TEXT("    where name = '%s'")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into projects (id, name, repo, owner_id, short_description , description)")
	  SQL_TEXT("select watch_proj.id, '%.40s', '%.2000s', org_select.id, '%.37s%s', '%.2000s'")
	  SQL_TEXT("from watch_proj, org_select;")
	  /* add homepage */
	  SQL_TEXT("with homepage_ins as (")
	  SQL_TEXT("    insert into websites (name, uri, descr)")
	  SQL_TEXT("    values ('Homepage', '%.250s', '%.400s')")
	  SQL_TEXT("    returning id")
	  SQL_TEXT("), watch_sel as (")
	  SQL_TEXT("    select * from projects")
	  SQL_TEXT("    where name = '%.40s'")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into watchables_sites (watchable_id, site_id)")
	  SQL_TEXT("select watch_sel.id, homepage_ins.id")
	  SQL_TEXT("from watch_sel, homepage_ins;")
	  ;

	char lic_statement[] =
	  /* license */
	  SQL_TEXT("with website_insert as (")
	  SQL_TEXT("    insert into websites (name, uri, descr)")
	  SQL_TEXT("    values ('%.40s', '%.250s', '%.400s')")
	  SQL_TEXT("    returning id")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into licenses (name, text_link)")
	  SQL_TEXT("select '%.40s', website_insert.id")
	  SQL_TEXT("from website_insert;")
	  /* link to license */
	  SQL_TEXT("with proj_sel as (")
	  SQL_TEXT("    select * from projects")
	  SQL_TEXT("    where name = '%.40s'")
	  SQL_TEXT("), lic_sel as (")
	  SQL_TEXT("    select * from licenses")
	  SQL_TEXT("    where name = '%.40s'")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into project_licenses (project_id, license_id)")
	  SQL_TEXT("select proj_sel.id, lic_sel.id")
	  SQL_TEXT("from proj_sel, lic_sel;");

	char download_mirror_statement[] =
	  SQL_TEXT("with proj_sel as (")
	  SQL_TEXT("    select * from projects")
	  SQL_TEXT("    where name = '%.40s'")
	  SQL_TEXT("), website_insert as (")
	  SQL_TEXT("    insert into websites (name, uri, descr)")
	  SQL_TEXT("    values ('%.40s', '%.250s', '%.400s')")
	  SQL_TEXT("    returning id")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into download_mirrors (project_id, site_id)")
	  SQL_TEXT("select proj_sel.id, website_insert.id")
	  SQL_TEXT("from proj_sel, website_insert;");

	char releases_str[400];
	snprintf(releases_str,
			 400,
			 "Github release page for %s",
			 string_name);

	char *buffer = malloc(10000 * sizeof(char));

	char homepage_str[400];
	snprintf(homepage_str,
			 400,
			 "Homepage for project %s",
			 string_name);
	char *cleaned_desc;
	if(string_description != NULL){
	   cleaned_desc= clean_value(string_description,
								 strlen(string_description));
	}else{
	  cleaned_desc = malloc(sizeof(char)*100);
	  strcpy(cleaned_desc, "No description");
	}
	snprintf(buffer,
			 10000,
			 statement,
			 org_name,
			 string_name,
			 string_repo,
			 cleaned_desc,
			 cleaned_desc != NULL ?
			 strlen(cleaned_desc) > 37 ? "..."
			 : ""
			 : "",
			 cleaned_desc != NULL ? cleaned_desc : "",
			 string_homepage,
			 homepage_str,
			 string_name);
	printf("%s", buffer);
	free(cleaned_desc);

	if(has_license){
	  snprintf(buffer,
			   10000,
			   lic_statement,
			   lic_name,
			   lic_html_url,
			   lic_descr,
			   lic_name,
			   string_name,
			   lic_name);
	  printf("%s", buffer);
	}

	snprintf(buffer,
			 10000,
			 download_mirror_statement,
			 string_name,
			 "Releases",
			 releases,
			 releases_str
			 );
	printf("%s", buffer);
			 
	free(buffer);

	handle_members(string_contributors,
				   string_name,
				   handle_user_proj);
  }
  return 0;
}

int
handle_org(int argc, char **argv){
  char *request_text;
  char url[URL_SIZE];
  json_t *request_root;
  json_error_t json_error;

  MACRO_json_string(repos_url);
  MACRO_json_string(members_url);
  MACRO_json_string(description);
  MACRO_json_string(name);
  MACRO_json_string(homepage);
  MACRO_json_string(github_uri);
  
  if(argc > 1){
	/* printf("fetching organization %s...\n", argv[1]); */
	snprintf(url, URL_SIZE, URL_ORG, argv[1]);
	
	request_text = request(url);
	if(!request_text){
	  printf("bad request.\n");
	  return 1;
	}

	request_root = json_loads(request_text, 0, &json_error);

	free(request_text);

	check_or_complain(!request_root, /* check not null */
					  "error on line %d, %s\n",
					  json_error.line,
					  json_error.text);

	check_or_complain(!json_is_object(request_root),
					  "root is not an object\n",
					  NULL, NULL);
	
	MACRO_json_get_string(request_root, name, "name");
	MACRO_json_get_string(request_root, repos_url, "repos_url");
	MACRO_json_get_string(request_root, homepage, "blog");
	MACRO_json_get_string(request_root, github_uri, "html_url");
	if(json_is_null(homepage)){
	  homepage = github_uri;
	  string_homepage = string_github_uri;
	}
	// this has {/member} in it - useless.
	//MACRO_json_get_string(request_root, users_url, "public_members_url");
	MACRO_json_get_string(request_root, description, "description");

	char string_members_url[256];
	snprintf(string_members_url,
			 256,
			 "https://api.github.com/orgs/%s/public_members",
			 argv[1]);

/* #define MACRO_json_print_string(var)			\ */
/* 	printf(#var":%s\n", json_string_value(var)); */
	
/* 	MACRO_json_print_string(repos_url); */
/* 	//MACRO_json_print_string(users_url); */
/* 	MACRO_json_print_string(description); */

	/* printf("Org data collected:\n"); */
	char statement[] =
	  SQL_TEXT("with watch_org as (")
	  SQL_TEXT("    insert into watchables")
	  SQL_TEXT("    default values")
	  SQL_TEXT("    returning id")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into organizations (id, name, homepage, short_description, description)")
	  SQL_TEXT("select watch_org.id, '%.40s', '%.40s', '%.37s%s', '%.2000s'")
	  SQL_TEXT("from watch_org")
	  SQL_TEXT("returning id;");

	char github_website[] =
	  SQL_TEXT("with org_sel as (")
	  SQL_TEXT("    select * from organizations")
	  SQL_TEXT("    where name = '%.40s'")
	  SQL_TEXT("), website_insert as (")
	  SQL_TEXT("    insert into websites (name, uri, descr)")
	  SQL_TEXT("    values ('%.40s', '%.250s', '%.400s')")
	  SQL_TEXT("    returning id")
	  SQL_TEXT(")")
	  SQL_TEXT("insert into watchables_sites (watchable_id, site_id)")
	  SQL_TEXT("select org_sel.id, website_insert.id")
	  SQL_TEXT("from org_sel, website_insert;");

	char *buffer = malloc(10000 * sizeof(char));
	snprintf(buffer,
			 10000,
			 statement,
			 
			 string_name,
			 string_homepage,
			 string_description != NULL ? string_description : "No Description",
			 string_description != NULL ? strlen(string_description) > 37 ? "..." : "" : "",
			 string_description != NULL ? string_description : "");
	printf("%s\n", buffer);

	char homepage_descr[400];
	snprintf(homepage_descr,
			 400,
			 "Github Homepage for %s",
			 string_name);
	snprintf(buffer,
			 10000,
			 github_website,
			 string_name,
			 "Github Project",
			 string_github_uri,
			 homepage_descr);
	printf("%s\n", buffer);
	free(buffer);
	/* MACRO_pretty_print_string(name); */
	/* MACRO_pretty_print_string(description); */
	/* printf("short_description:%40s\n", string_description); */
	/* MACRO_pretty_print_string(homepage); */
	/* MACRO_pretty_print_string(members_url); */
	/* MACRO_pretty_print_string(repos_url) */;

	handle_members(string_members_url, string_name, handle_user_org);
	handle_repos(string_repos_url, string_name);
	
	return 0;
  } else {
	printf("bad args\n");
	return 1;
  }
}

int
main(int argc, char **argv) {
  /* if(argc != 3){ */
  /* 	fprintf(stderr, "usage: %s USER REPOSITORY\n\n", argv[0]); */
  /* 	fprintf(stderr, "List commits at USER's REPOSITORY.\n\n"); */
  /* 	return 2; */
  /* } */
  /* for(int i = 0; */
  /* 	  i < argc; */
  /* 	  i++){ */
  /* 	printf("arg[%d]:%s\n", i, argv[i]); */
  /* } */
  
  return handle_org(argc, argv);
}
