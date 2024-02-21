MYSQL:
--------------------------------
Username: admin

Password: jE5DrMnj3mU0

Docker Image:
--------------------------------
tag: liam/fruckr:latest

docker build -t liam/fruckr:latest .

docker run -p "8080:80" -v ${PWD}/codeigniter:/app -v ${PWD}/mysql:/var/lib/mysql liam/fruckr:latest

Test Data:
--------------------------------
Email:						Password:

- liam16juni2004@gmail.com	test
- liamfroyen@gmail.com		test
- sophie.beertens@gmail.com	test
- sarah.beerten@gmail.com		test
- zoe.fertig@gmail.com		test
- elketomon@gmail.com			test
