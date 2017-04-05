FROM brucepc/mapguide

ENV DEBIAN_FRONTEND noninteractive

# ---------
# MULTIVERSE
# ---------
RUN apt-get update && \
    apt-get install -y --no-install-recommends software-properties-common wget unzip curl && \
    apt-add-repository multiverse && \
    apt-get update

# ---------
# MS CORE FONTS
# ---------
# from http://askubuntu.com/a/25614
RUN echo "ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true" | debconf-set-selections
RUN apt-get install -y --no-install-recommends fontconfig ttf-mscorefonts-installer
# ADD localfonts.conf /etc/fonts/local.conf
# RUN fc-cache -f -v

ARG MG_DOWNLOAD_URL=http://download.osgeo.org/mapguide/releases/3.0.0/extras/Sheboygan.mgp
ENV MG_INSTALL=/usr/local/mapguideopensource-3.1.0
ENV MG_REST_INSTALL=$MG_INSTALL/webserverextensions/www/rest
ENV PATH $PATH:$MG_INSTALL/webserverextensions/php/bin

RUN mkdir -p $MG_REST_INSTALL

COPY app $MG_REST_INSTALL/app/
COPY cache $MG_REST_INSTALL/cache/
COPY assets $MG_REST_INSTALL/assets/
COPY conf $MG_REST_INSTALL/conf/
COPY doc $MG_REST_INSTALL/doc/
COPY sampleapps $MG_REST_INSTALL/sampleapps/
COPY test $MG_REST_INSTALL/test/
COPY .htaccess $MG_REST_INSTALL/
COPY index.php $MG_REST_INSTALL/
COPY composer.* $MG_REST_INSTALL/
COPY build.phing.xml $MG_REST_INSTALL/

RUN wget $MG_DOWNLOAD_URL -O $MG_REST_INSTALL/sampleapps/data/Sheboygan.mgp
#RUN echo $PATH
RUN cd $MG_REST_INSTALL && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    php composer.phar install

RUN chown daemon:daemon $MG_REST_INSTALL/cache && \
    sed -i 's/display_errors = Off/display_errors = On/g' $MG_INSTALL/webserverextensions/php/lib/php.ini
ADD demo/index.php $MG_REST_INSTALL/sampleapps/