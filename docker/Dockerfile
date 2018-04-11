FROM oraclelinux:6.8
MAINTAINER "Ed"
RUN rpm -ivh http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm >/tmp/epel.rpm
RUN yum -y install \
	mod_fcgid        \
	httpd            \
	perl             \
	perl-FCGI        \
	perl-CGI         \
	perl-Image-Size  \
	perl-DBD-Pg      \
	perl-File-Slurp
RUN rpm --import http://li.nux.ro/download/nux/RPM-GPG-KEY-nux.ro
RUN rpm -Uvh http://li.nux.ro/download/nux/dextop/el6/x86_64/nux-dextop-release-0-2.el6.nux.noarch.rpm
RUN yum -y install ffmpeg
RUN mkdir -p /usr/local/data
RUN ln -s /media /var/www/html
RUN printf '<Directory /var/www/html>\nAllowOverride All\n</Directory>\n\n' | tee -a /etc/httpd/conf/httpd.conf
COPY local_fcgid.conf /etc/httpd/conf.d/
COPY .htaccess *.html /var/www/html/
COPY *.fcgi /var/www/cgi-bin/
COPY add_media.pl locate_media.pl scan.sh /usr/local/bin/
COPY media_common.inc /usr/local/data/
EXPOSE 80
ENTRYPOINT /usr/sbin/httpd -D FOREGROUND
