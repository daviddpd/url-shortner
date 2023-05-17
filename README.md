# url-shortner

url-shortner is a simple URL shortener for PHP - aka make your own "go" links.

(I renamed from shorty, as I think it sounds too much like Smarty, which is a templating system for PHP.) 

## Installation

1\. Download and extract the files to your web directory.

2\. Use the included `database.sql` file to create a table to hold your URLs.

3\. Configure your webserver.

For **Apache**, edit your `.htaccess` file with the following (this is now included in this fork).

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?q=$1 [QSA,L]

4\. Edit the `config.php` file.

## Generating short URLs

To generate a short URL, simply pass in a `url` query parameter to your Shorty installation:

    https://go.local/?url=http://www.google.com

This fork has support for vanity URLs, v for vanity:

    https://go.local/?url=https://davidpdischer.com&v=dpdHomePage

This will return a shortened URL such as:

    https://go.local/bwIr"
    https://go.local/dpdHomePage
    
If the MD5 hash of the URL is matches something in the DB, it will be returned, not updated. 
    
```
    *************************** 4. row ***************************
      id: 21
     url: https://davidpdischer.com
 created: 2023-05-17 17:48:20
accessed: 2023-05-17 17:48:46
    hits: 1
   short: 41db7b941880537e7ab162f90a736216
  vanity: dpdHomePage
  md5url: cbeed44a7fbd81edd466267e6b0b85da
```    
    
When a user opens the short URL they will be redirected to the long URL location, and 
the hits count is updated. Scaling this, one may consider removing the hits (just log this
and use something async to count it.)

By default, Shorty will generate an HTML response for all saved URLs.
You can alter the response format by passing in a `format` query parameter.

    http://example.com/?url=http://www.google.com&format=text

The possible formats are `html`, `xml`, `text`, and `json`.

## Access Control 

I've removed the allow() function. Please use something robust - I 
prefer doing this in the Apache HTTP server, as pre-auth, this avoids
running any PHP calls or other scripting languages. 

    OpenID : 
        https://github.com/OpenIDC/mod_auth_openidc
    Basic SSO, plugs into LDAP: 
        https://github.com/daviddpd/mod_auth_pubtkt
    mTLS : Mutual TLS or Client Certificate Authentication
        https://httpd.apache.org/docs/2.4/ssl/ssl_howto.html
        https://smallstep.com/hello-mtls

## Requirements

* PHP 5.1+
* PDO extension (which one?)

## License

Shorty is licensed under the [MIT](https://github.com/daviddpd/url-shortner/blob/dpdfork/LICENSE) license.
