# Message Board API

Basic demo app following the requirements from [REQUIREMENTS.md](REQUIREMENTS.md)

I've written the application as a standard REST application, using the `Route::apiResource` method to map into standard
Controller method names. I've placed authorizations in the Controllers, since it's more readable than in the Routes,
and implemented basic Policies to handle them.

Given the scope of the application I left the basic Eloquent queries in the Controllers. I planned for adding basic
caching to their response, but that's always tricky to invalidate correctly, and didn't want to take more time.

For the search I used `Scout`, and initially used `MeiliSearch`, installed through `Docker`. The search worked fine, but
there was a bug when trying to filter by `user_id`, even though I was passing and configuring that correctly. After some
investigation it seems to be a bug related with `MeiliSearch`, so I switched the `driver` to the `Collection` engine,
which is suitable for development.

Aside from the regular CRUD operations, whenever a new `Message` is created a `MessageCreated` event is fired, which is
linked with the `NotifyThreadUsers` listener, that queues the `SendNewMessagesNotificationToThreadUsers` job to be
dispatched in `1 minute`. The Job implements `ShouldBeUnique` linked to the `Thread` id, so any subsequent created
messages in the same thread will trigger the event, but the listener will not add new jobs. The job will fetch the
message and any newer that might be present, fetch all the users to notify and send the emails, which are also queued
immediately in order to free the job.

There's no additional feature added per se. I chose to make use of many `Laravel` features, like `Events`, `Policies`,
`FormRequests`, libraries and tools like `Scout`, `Horizon`, `Clockwork` and write proper automated testing.

## Install

1. Clone the repo
2. Have `Docker` installed and running
2. `composer install && npm install`
3. Copy `.env.example` to `.env`
4. `vendor/bin/sail up -d` to initialize the containers
5. `vendor/bin/sail php artisan migrate` to bootstrap the database schema

## Run

* Run `vendor/bin/sail php artisan serve` to run the web application
* Open [http://localhost:8025/](http://localhost:8025/) to review emails sent by the application
* Open [http://localhost/clockwork/](http://localhost/clockwork/) to monitor requests sent to the application, performance, sql and redis queries,
  etc
* Run `vendor/bin/sail php artisan horizon` to start the queue jobs and use the Horizon web UI to monitor them
* Open [http://localhost/horizon/dashboard](http://localhost/horizon/dashboard) to view the Horizon web UI

## Test

For automated testing run `vendor/bin/sail php artisan test` to run the Feature tests on the API. This are basic
API consumtion test, with some checks for validation and authorization. There're more features to test that I haven't
implemented.

For manual Testing install `Insomnia` app, import [config/insomnia.json](config/insomnia.json) and test the services within the app.
