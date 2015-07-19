var http = require('http');

var server = http.createServer(function (request, response) {
	response.writeHead(200, {"Content-Type": "text/plain"});
	response.end("This is a NodeJS http test");
});

server.listen(8000);

console.log("Server running at http://0.0.0.0:8000/");