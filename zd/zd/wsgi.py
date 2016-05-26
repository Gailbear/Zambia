"""
WSGI config for zd project.

It exposes the WSGI callable as a module-level variable named ``application``.

For more information on this file, see
https://docs.djangoproject.com/en/1.8/howto/deployment/wsgi/
"""

# import os

# from django.core.wsgi import get_wsgi_application

# os.environ.setdefault("DJANGO_SETTINGS_MODULE", "zd.settings")
#
# application = get_wsgi_application()
import os
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "zd.settings")
from django.core.wsgi import get_wsgi_application
application = get_wsgi_application()
from zd.middleware import PrefixMiddleware
application = PrefixMiddleware(application, prefix='/zd')