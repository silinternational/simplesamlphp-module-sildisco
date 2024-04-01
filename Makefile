suite: dynamo
	docker-compose up -d hub sp1 sp2 sp3 idp1 idp2 idp3

bash:
	docker-compose exec hub bash

clean:
	docker-compose kill
	docker-compose rm -f

unittests:
	docker-compose run --rm unittest /data/run-tests.sh

functionaltests:
	docker-compose run --rm browsertest

composer:
	docker-compose run --rm composer bash -c "/data/update-composer-deps.sh"

composertests:
	docker-compose run --rm browsertest bash -c "/data/update-composer-deps.sh"

dynamo:
	docker-compose up -d dynamo
	sleep 5
	docker-compose run init-dynamo
