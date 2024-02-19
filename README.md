# VOIP-Application

### Description

Application built with Laravel 10. It includes a call management system that processes incoming calls.

### Prerequisites

- PHP >= 8.2.0
- Composer >= 2.7.1
- Docker Desktop >= 4.27.2 (Docker version 25.0.3 && Docker Compose version v2.24.5-desktop.1)

1. Create a Ngrok account https://ngrok.com and get a new Static Domain
2. Run in terminal `ngrok http --domain=<static domain from ngrok> 80`
3. Create a Twilio account https://www.twilio.com and rent a new phone number.
4. Go to PhoneNumbers->Manager->ActiveNumbers and configure the phone numnber.
5. Under Voice Configuration: 
    * A comes in: `<webhook url>` `HTTP POST`
    * Call status changes: `<webhook url>` `HTTP POST`

### Installation

1. Clone the repository: `git clone https://github.com/keynertyc/voip-test-app.git`
2. Navigate to the project directory: `cd voip-test-app`
3. Install dependencies: `composer install`
4. Copy the `.env.example` file to create your own `.env` file: `cp .env.example .env`
5. Set up your Twilio credentials in the `.env` file

```
CPAAS_PROVIDER=twilio
TWILIO_SID=<from twilio>
TWILIO_TOKEN=<from twilio>
TWILIO_NUMBER=<from twilio>
TWILIO_AGENT_NUMBER=+51941884743
```

6. Start Application with Sail: `./vendor/bin/sail up`

```
Using Docker, it will install Mysql, Laravel Application
```

7. Generate an application key: `./vendor/bin/sail artisan key:generate`
8. Run the migrations: `./vendor/bin/sail artisan migrate`

### Use

Call from a Verified Number in Twilio to the Rented Number `TWILIO_NUMBER` and listen the instructions given by the call.

### Unit Testing

Run tests: `./vendor/bin/sail pest`
