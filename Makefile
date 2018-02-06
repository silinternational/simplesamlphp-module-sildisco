suite:
	docker-compose up -d hub sp1 sp2 idp1 idp2 idp3

bash:
	docker-compose exec hub bash

clean:
	docker-compose kill
	docker-compose rm -f

unittests:
	docker-compose run --rm unit_test /data/run-tests.sh

functionaltests:
	docker-compose run --rm browser_test /data/codecept run
