
all:
	scp gallery.fcgi index.fcgi hpmicro:/pool0/www/fcgi/
	echo "pgrep -f index; sudo pkill -9 -f index; sudo pkill -f gallery" \
		| ssh hpmicro -t /bin/bash
