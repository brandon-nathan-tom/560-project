curl --fail -s "https://www.openhub.net/accounts/$EMAIL_MD5.xml?v=1&api_key=$API_KEY" | sed -n 's/ *<\(.*\)>\(.*\)<\/\1>$/\1: \2/ p'

# urn:ietf:wg:oauth:2.0:oob
# 45ae82846f5fd124f64d7470c0fee865d133195e9bad7fa1b3e82de3a2017376
