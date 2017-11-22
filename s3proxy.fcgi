#!/usr/bin/env python
#-------------------------------------------------------------------------------
# Author: Lukasz Janyst <lukasz@jany.st>
# Date: 22.11.2017
#-------------------------------------------------------------------------------

import os
import mimetypes
import configparser
import botocore.session
from urllib.parse import parse_qs
from flup.server.fcgi_fork import WSGIServer

#-------------------------------------------------------------------------------
def feed(environ, start_response):
    #---------------------------------------------------------------------------
    # Read the commandline parameters
    #---------------------------------------------------------------------------
    params = parse_qs(environ.get('QUERY_STRING', ''))
    config_name = params.get('config', ['default'])[0]
    bucket = params.get('bucket', ['None'])[0]
    key = params.get('key', ['None'])[0]

    #---------------------------------------------------------------------------
    # Get the configuraion
    #---------------------------------------------------------------------------
    try:
        path = '~/.s3proxyrc'
        path = os.path.expanduser(path)
        config = configparser.ConfigParser()

        with open(path, 'r') as f:
            config.read_file(f)
        config[config_name]['aws-key-id']
        config[config_name]['aws-key-secret']
    except Exception as e:
        msg = '{}: {}\n'.format(type(e).__name__, str(e))
        start_response('401 Unauthorized',
                       [('Content-Type', 'text/plain; charset=utf-8')])
        return [msg.encode('utf-8')]

    #---------------------------------------------------------------------------
    # Get the data
    #---------------------------------------------------------------------------
    key_id = config[config_name]['aws-key-id']
    key_secret = config[config_name]['aws-key-secret']

    try:
        session = botocore.session.get_session()
        s3_client = session.create_client('s3', aws_access_key_id=key_id,
                                          aws_secret_access_key=key_secret)
        obj = s3_client.get_object(Bucket=bucket, Key=key)
        data = obj['Body'].read()
    except Exception as e:
        msg = '{}: {}\n'.format(type(e).__name__, str(e))
        start_response('401 Unauthorized',
                       [('Content-Type', 'text/plain; charset=utf-8')])
        return [msg.encode('utf-8')]

    #---------------------------------------------------------------------------
    # Send the data
    #---------------------------------------------------------------------------
    content_type = mimetypes.guess_type(key)
    start_response('200 OK', [('Content-Type', content_type[0])])
    return [data]

if __name__ == '__main__':
    WSGIServer(feed).run()
