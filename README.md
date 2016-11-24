[Nov. 15th JSHall Talk] Sample eStore App

*Requirements : redis-server (latest version).*

Just run npm install && php composer.phar install and you're good to go :)

*Cache Hit/Miss Benchmarks :*

./ab.bash -n100 -c10 http://[path_to_endpoint]/api/products?forceMiss=1 http://[path_to_endpoint]/api/products