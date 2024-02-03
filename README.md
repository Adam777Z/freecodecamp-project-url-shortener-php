**freeCodeCamp** - APIs and Microservices Project
------

**URL Shortener Microservice**

### User Stories:

1. I can POST a URL to `[project_url]/api/shorturl` and I will receive a shortened URL in the JSON response. Example: `{"original_url":"https://www.freecodecamp.org","short_url":"jf71vgvg3n"}`
2. If I pass an invalid URL that doesn't follow the valid `http(s)://(www.)example.com(/more/routes)` format, the JSON response will contain an error like `{"error":"invalid URL"}`.
3. When I visit the shortened URL, it will redirect me to my original URL.

#### Creation Example:

POST /api/shorturl - body (urlencoded): url=https://www.freecodecamp.org

#### Usage:

/api/shorturl/jf71vgvg3n will redirect to https://www.freecodecamp.org