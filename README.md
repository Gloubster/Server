# Gloubster Server

[![Build Status](https://travis-ci.org/Gloubster/Server.png?branch=master)](https://travis-ci.org/Gloubster/Server)

## Requirements

// todo

## Installation

First, clone the project :

```bash
git clone https://github.com/Gloubster/Server.git gloubster-server
```

Gloubster Server relies on many components (Bower, composer, curl, etc...), to
init this, use the easy command :

```bash
cd gloubster-server
./init.sh
```

## Concepts

Gloubster server listens for incoming job messages.
These jobs are queued in a RabbitMQ server.
Once processed by a worker, the message is sent back to a log queue.
Log queue is processed to store processed data information in a Redis server for reporting purpose.
A web application is available, providing realtime monitoring through websockets.

## Configuration

You will find a configuration sample in `config/config.sample.json`.

### server

// todo

### redis-server

// todo

### session-server

// todo

### websocket-server

// todo

### listeners

// todo


## Defining my own listener

// todo

## Hacking GloubsterServer

GloubsterServer is a Dependency Container itself and embeds Evenement as
event-dispatcher.

You can easily hack it :

```php
use Gloubster\Server\GloubsterServer;

$gloubster = new GloubsterServer::create($loop, $conf, $logger);
$gloubster['dispatcher']->on('start', function () {
    echo "Gloubster server has start\n";
});
```

Be free, but remember you **must** be non-blocking, bro !

Available events are :

 * start : triggered when the run method is called.
 * booted : triggered once all service (stomp, redis, websockets) are connected and fine.
 * stop : triggered when the stop method is called.
 * error : triggered whenever an error occured.
 * stomp-connected : triggered once stomp client is connected.
 * redis-connected : triggered once redis client is connected.

## Todo

 * Add priority
 * Real web application

## License

Released under the MIT license.
