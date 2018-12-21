# Url Twig Extension

The url twig extension gives you a simple and efficient way to get specific parts of urls.

## Setup

### Service Registration

The twig extension need to be registered as [symfony service](http://symfony.com/doc/current/service_container.html).

**xml**

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services
        http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <service id="app.twig.web_url" class="Massive\Component\Web\UrlTwigExtension">
            <tag name="twig.extension" />
        </service>
    </services>
</container>
```

**yml**

```yml
services:
    app.twig.web_url:
        class: Massive\Component\Web\UrlTwigExtension
        tags:
            - { name: twig.extension }
```

## Usage

### Format url

You can get a specific format of any valid url

```twig
{{ 'https://www.example.org/test'|url_format({
    scheme: false,
    path: false,
}) }}
```

Output:

```html
www.example.org
```

### Get scheme

You can get the scheme of a valid url

```twig
{{ 'https://www.example.org/test'|url_scheme }}
```

Output:

```html
https
```

### Get user

You can get the user of a valid url

```twig
{{ 'ftp://admin:hidden@example.org'|url_user }}
```

Output:

```html
admin
```

### Get password

You can get the password of a valid url

```twig
{{ 'ftp://admin:hidden@example.org'|url_pass }}
```

Output:

```html
hidden
```

### Get host

You can get the host of a valid url

```twig
{{ 'example.org:8080'|url_host }}
```

Output:

```html
example.org
```

### Get port

You can get the port of a valid url

```twig
{{ 'example.org:8080'|url_port }}
```

Output:

```html
8080
```

### Get path

You can get the path of a valid url

```twig
{{ 'http://example.org/hello'|url_path }}
{{ 'example.org/hello'|url_path }}
{{ 'example.org:8080/hello'|url_path }}
```

Output:

```html
hello
example.org/hello
hello
```

### Get query

You can get the query of a valid url

```twig
{{ 'http://example.org/hello?arg=test&x=y'|url_query }}
```

Output:

```html
arg=test&x=y
```

### Get fragment

You can get the fragment of a valid url

```twig
{{ 'http://example.org/hello#test'|url_query }}
```

Output:

```html
test
```
