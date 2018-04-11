CONTAINER=ol68tube
NETWORK=network1
DB=tubedb
MEDIAROOT=/mnt/freenas

help:

all: db container populatedb

network:
	sudo docker network create $(NETWORK)

db:
	-sudo docker rm -f $(DB)
	sudo docker run --name $(DB) -e POSTGRES_PASSWORD=tubepassword --restart always -p 5432:5432 -d postgres
	echo 'printf "alias db=\"psql -U tube $(DB)\"\nset -o vi\n" >/root/.bashrc;' | sudo docker exec -i $(DB) /bin/bash
	echo 'printf "set editing-mode vi\n" >~/.inputrc;' | sudo docker exec -i $(DB) /bin/bash
	sleep 5
	cat createdb.sql | sudo docker exec -i $(DB) psql -U postgres


populatedb:
	echo 'find /media -type f | perl /usr/local/bin/add_media.pl' \
	| sudo docker exec -i $(CONTAINER) /bin/bash

container:
	-sudo docker rm -f $(CONTAINER)
	sudo docker build --tag $(CONTAINER) .
	sudo docker create --name $(CONTAINER) --restart=always -v $(MEDIAROOT)/films:/media/films -v $(MEDIAROOT)/tv:/media/tv -v $(MEDIAROOT)/mp3:/media/mp3 -p 80:80 $(CONTAINER)
	sudo docker start $(CONTAINER)

run:
	sudo docker start $(CONTAINER)

exec:
	sudo docker exec -it $(CONTAINER) tail -f /var/log/httpd/error_log
	
shell:
	sudo docker exec -it $(CONTAINER) /bin/bash

