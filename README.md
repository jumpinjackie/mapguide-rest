mapguide-rest
=============

[![Build Status](https://travis-ci.org/jumpinjackie/mapguide-rest.svg)](https://travis-ci.org/jumpinjackie/mapguide-rest)

mapguide-rest is a RESTful web extension for [MapGuide Open Source](http://mapguide.osgeo.org) that continues the ideas explored in [mapguide4j](https://github.com/jumpinjackie/mapguide4j) and [GeoREST](https://code.google.com/p/georest/) projects

mapguide-rest provides the following services:

 - A REST-ful http interface modeled on original discussions on a [RESTful web service for MapGuide](http://trac.osgeo.org/mapguide/wiki/Future/RESTfulWebServices)
 - A re-imagining of GeoREST and its data publishing capabilities

mapguide-rest is written in PHP and uses the following libraries:

 - [Slim Framework](http://www.slimframework.com/)
 - [Smarty Template Engine](http://www.smarty.net)

**WARNING: mapguide-rest is currently experimental and should only be used for testing/development purposes. Use at your own risk on production data**

Requirements
============

mapguide-rest requires an installation of MapGuide Open Source and its bundled copy of PHP. As PHP is required by Fusion and other installed MapGuide web applications, this requirement will almost always be satisifed.

mapguide-rest has been tested on MapGuide Open Source 2.6. It should theoretically work on any installation of MapGuide Open Source or AIMS that includes PHP 5.3 or newer.

Setup
=====

**NOTE: For convenience, you should use one of the pre-packaged [release distributions](https://github.com/jumpinjackie/mapguide-rest/releases)**

 1. Ensure you have a git and svn client installed and this is accessible from the command-line.

 2. Clone this repository into the www directory of your MapGuide Open Source installation. Rename the clone directory to "rest" (eg. C:\Program Files\OSGeo\MapGuide\Web\www\rest on windows or /usr/local/mapguideopensource-2.6.0/webserverextensions/www/rest on linux)

 3. Install [PHP Composer](https://getcomposer.org/) if you have no done so already

 4. Run the following composer command from the root of your clone to pull down required vendor libraries
 > composer install
 
 or
 
 > php composer.phar install

 Some of the external libraries that composer pulls down may require a svn checkout or git clone, so ensure you have these clients installed in step 1.

 5. [OPTIONAL] Install and enable the [Application Request Routing module for IIS](http://www.iis.net/downloads/microsoft/application-request-routing). This allows for "cleaner" URLs. If you have installed the ARR module, you can rename the web.config.iis file to web.config to activate ARR for the REST extension. For Apache, you can use the provided .htaccess file for clean URLs and skip this step.

 6. [OPTIONAL] On Linux, you may need to create a "templates_c" folder under the "cache" directory and give it sufficient permissions for the Smarty template engine to save compiled templates to. See [Example 2.7 of the Smarty installation guide](http://www.smarty.net/docsv2/en/installing.smarty.basic.tpl) for more information. Additionally, Smarty may also complain about a default timezone not being set (this is raised as an exception). To avoid this exception, [set a default timezone in php.ini and restart Apache](http://au2.php.net/manual/en/datetime.configuration.php#ini.date.timezone)

 7. You can now access the REST endpoint at:
 
  - http://yourservername:port/mapguide/rest
  - http://yourservername:port/mapguide/rest/index.php (if you don't have ARR installed for IIS or mod_rewrite enabled for Apache)

Documentation
=============

Check out the [wiki](https://github.com/jumpinjackie/mapguide-rest/wiki) for additional information and documentation.

License
=======

mapguide-rest is licensed under the GNU Lesser General Public License (LGPL) v2.1 (see LICENSE for more information)
