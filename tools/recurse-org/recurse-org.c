#include <jansson.h>
#include <string.h>
#include <stdlib.h>
#include <stdio.h>
#include <curl/curl.h>

#define BUFFER_SIZE  (256 * 1024)  /* 256 KB */
#define URL_FORMAT "https://api.github.com/repos/%s/%s/commits"
#define URL_SIZE 256

#define SSL_OPTIONS CURLSSLOPT_NO_REVOKE

#define check_or_complain(check, complaint, arg1, arg2)	\
  if(check){											\
	fprintf(stderr, complaint, arg1, arg2);				\
	json_decref(root);									\
	return 1;											\
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
    headers = curl_slist_append(headers, "User-Agent: Jansson-Tutorial");
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
main(int argc, char **argv) {
  size_t i;
  char *text;
  char url[URL_SIZE];
  json_t *root;
  json_error_t error;

  if(argc != 3){
	fprintf(stderr, "usage: %s USER REPOSITORY\n\n", argv[0]);
	fprintf(stderr, "List commits at USER's REPOSITORY.\n\n");
	return 2;
  }

  snprintf(url, URL_SIZE, URL_FORMAT, argv[1], argv[2]);

  text = request(url);
  if(!text){
	return 1;
  }

  root = json_loads(text, 0, &error);
  free(text);

  check_or_complain(!root, "error: on line %d: %s\n", error.line, error.text);

  check_or_complain(!json_is_array(root), "error: root is not an array.\n\n", NULL, NULL);

  for(i=0;
	  i<json_array_size(root);
	  i++){
	json_t *data, *sha, *commit, *message;
	const char *message_text;

	data = json_array_get(root, i);
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

  json_decref(root);
  return 0;
}
