<?php

namespace App\Services;

use Twilio\Rest\Client;


class TwilioSmsService
{
  protected $client;
  protected $twilioNumber;

  public function __construct()
  {
      $this->client = new Client(config('twilio.sid'), config('twilio.token'));
      $this->twilioNumber = config('twilio.number');
  }

  public function sendSms($to, $message)
  {
      return $this->client->messages->create(
          $to, // Receiver's phone number
          [
              'from' => $this->twilioNumber,
              'body' => $message
          ]
      );
  }
}


