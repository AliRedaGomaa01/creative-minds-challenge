<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FCMService
{
  public static function sendNotification($deviceToken, $title, $body, $data = [])
  {
    $messaging = Firebase::messaging();

    $message = CloudMessage::withTarget('token', $deviceToken)
      ->withNotification(Notification::create($title, $body))
      ->withData($data);

    return $messaging->send($message);
  }

  public function sendPushNotificationUsingDeviceToken()
  {
    //  preferred for less than 500 user 

    $deviceTokens = User::whereNotNull('device_token')->pluck('device_token')->toArray();

    if (empty($deviceTokens)) {
      return response()->json(['message' => 'No devices found.']);
    }

    $title = "Global Alert!";
    $body = "This is a message for all users.";
    $data = ['info' => 'static_notification'];

    // Send notification to all devices
    $response = FCMService::sendNotification($deviceTokens, $title, $body, $data);

    return response()->json(['message' => 'Notification sent to all users!', 'response' => $response]);
  }

  public function sendToTopic($topic, $title, $body, $data = [])
  {
    //  preferred for large user numbers 
    $messaging = Firebase::messaging();

    $message = CloudMessage::withTarget('topic', $topic)
      ->withNotification(Notification::create($title, $body))
      ->withData($data);

    return $messaging->send($message);
  }

  public function sendPushNotificationToAllUsingTopic()
  {
    // subscribe user to this topic  
    // Firebase::messaging()->subscribeToTopic('global_notifications', $deviceToken);

    $title = "Global Announcement!";
    $body = "This is a static message for all users.";
    $data = ['info' => 'important'];

    $response = FCMService::sendToTopic('global_notifications', $title, $body, $data);

    return response()->json(['message' => 'Notification sent to all users!', 'response' => $response]);
  }
}


