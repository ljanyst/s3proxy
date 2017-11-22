
s3proxy
=======

*s3proxy* is a [FastCGI][fcgi] script that reads data from a protected AWS S3
bucket and makes it available to the user. It's useful when you want to have some of your
data accessible with HTTP Authentication, which is not supported by AWS. The script tries
to guess the MIME content type using the key name. It also reads all the data from the
source at once and does not support partial content, so it's probably not a good idea
to use it with huge files.

Installation
------------

*s3proxy* depends on `python3`, `flup6` for the FastCGI interface, and `botocore` for
AWS access. The easiest way to install the last two is with `pip`:

    pip install flup6
    pip install botocore

You may find [Envy][envy] useful.

Place the `s3proxy.fcgi` file wherever your web server can see it and create the
`~/.s3proxyrc` configuration file. The configuration file contains one or more
sections with your AWS credentials:

    ]==> cat ~/.s3proxyrc
    [default]
    aws-key-id = some-key-id
    aws-key-secret = some-key-secret

Usage
-----

The script accepts three HTTP GET parameters:

 * `bucket` - S3 bucket name
 * `key` - a valid key name within the bucket
 * `config` - optional - denotes the configuration section containing the
   credentials

You can use it with a browser or any other HTTP client:

    ]==> wget --ask-password "https://user@website.com/s3proxy.fcgi?bucket=my-bucket&key=my-key.xml"

Don't forget to set the `.htaccess` up accordingly. Otherwise, you may just use S3 directly :)

[fcgi]: https://en.wikipedia.org/wiki/FastCGI
[envy]: https://github.com/ljanyst/envy
