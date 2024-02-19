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

## Software Architecture

This application was developed using the `Facade and Strategy Design Patterns`.

This allows me to create a class `CallManager.php`, as a facade and create a service `TwilioProvider.php`, that implements the `CallProviderInterface.php` contract.

This enables us, in the future, to change or implement another CPASS Provider such as VONAGE or any other. Following the Strategy Design Pattern, we would create a `VonageProvider.php` class that implements the methods of the `CallProviderInterface.php` contract. Simply changing it in the configuration file would make everything transparent since `CallManager.php` is the class that manages the functionality under Dependency Injection of the CallProviderInterface within the constructor.

Additionally, it is registered within the Service Container/Provider: AppServiceProvider and CallServiceProvider

## Application Flow

1. A call is made from a number to the rented Twilio number.
2. Twilio launches it to the configured webhook (which is our application).
3. We receive the information and generate the voice response (from the code) with the two options (forward call or leave a voicemail).
4. If option 1 (forward call) is selected, we forward it to the defined agent's number.
5. If option 2 (leave a voicemail) is selected, a beep will be heard, and the person will speak, recording the message. Finally, we send "Thank you, goodbye," and close the call.
6. As we also have the CallStatus webhook configured, when the call ends, it launches to the webhook (another endpoint) where we receive all the final status of the call and record it in the database.
