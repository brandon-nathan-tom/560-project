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

#define check_or_complain(check, complaint, arg1, arg2)	\
  if(check){											\
	fprintf(stderr, complaint, arg1, arg2);				\
	json_decref(request_root);									\
	return 1;											\
  }


#define MACRO_json_get_string(thing, var, field)	\
  var = json_object_get(thing,						\
						field);						\
  check_or_complain(!json_is_string(var),			\
					field " is not a string\n",		\
					NULL, NULL);					\
  string_ ## var = json_string_value(var);

#define MACRO_pretty_print_string(var)			\
  printf(#var":%s\n", string_ ## var);	

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

	printf("requesting url:'%s'\n", url);

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

int
handle_user(const char *user_url){
  printf("url:'%s'\n", user_url);
  MACRO_verbose_request(user_url);
  return 0;
}

int
handle_members(const char *members_url){
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
	handle_user(string_user_url);
	return 0;
  }
  return 0;
}

int
handle_repos(const char *repos_url){
  printf("skipping fetching repositories...\n");
  return 0;
  MACRO_silent_request(repos_url);
  MACRO_print_request();
  return 0;
}

int
handle_org(int argc, char **argv){
  char *request_text;
  char url[URL_SIZE];
  json_t *request_root;
  json_error_t json_error;

  const char *string_repos_url;
  json_t *repos_url;
  const char *string_members_url;
  json_t *members_url;
  const char *string_description;
  json_t *description;
  
  if(argc > 1){
	printf("fetching organization %s...\n", argv[1]);
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
	
	MACRO_json_get_string(request_root, repos_url, "repos_url");
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

	printf("Data collected:\n");
	MACRO_pretty_print_string(description);
	MACRO_pretty_print_string(members_url);
	MACRO_pretty_print_string(repos_url);

	handle_members(string_members_url);
	handle_repos(string_repos_url);
	
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
  for(int i = 0;
	  i < argc;
	  i++){
	printf("arg[%d]:%s\n", i, argv[i]);
  }

  return handle_org(argc, argv);
}
