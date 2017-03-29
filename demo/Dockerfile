FROM brucepc/mapguide

ENV DEBIAN_FRONTEND noninteractive

# ---------
# MULTIVERSE
# ---------
RUN apt-get update
RUN apt-get install -y --no-install-recommends software-properties-common wget unzip curl
RUN apt-add-repository multiverse
RUN apt-get update

# ---------
# MS CORE FONTS
# ---------
# from http://askubuntu.com/a/25614
RUN echo "ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true" | debconf-set-selections
RUN apt-get install -y --no-install-recommends fontconfig ttf-mscorefonts-installer
# ADD localfonts.conf /etc/fonts/local.conf
# RUN fc-cache -f -v

RUN mkdir -p /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest
RUN wget http://download.osgeo.org/mapguide/releases/3.0.0/extras/Sheboygan.mgp -O /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/test/data/Sheboygan.mgp

ADD app /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/app
ADD assets /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/assets
ADD conf /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/conf
ADD doc /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/doc
ADD sampleapps /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/sampleapps
ADD .htaccess /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/
ADD index.php /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/
ADD composer.* /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/
ADD build.phing.xml /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/
ADD setup.sh /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/
RUN /usr/local/mapguideopensource-3.1.0/webserverextensions/www/rest/setup.sh