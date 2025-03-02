# Challenge description : 
### Please make the following task using laravel 8+ (latest build):
<pre>
  1- Install Laravel Telescope and make sure to log all actions and error. Log every thing and send the full db dump with the task (mandatory).
  2- Install Laravel Firbase Package(FCM) to send push notification. (mandatory)
  3- Make all authentications with JWT.
  4- Register a new account using mobile number, password, username, location on google maps, image with thumbinal (use twilio sms gateway to send sms to verify mobile number).
  5- Login API with mobile number and password.
  6- Create API for getting profile data.
  7- Create API for getting nearest Delivery representatives by calculate distance between their locations and user location ("locations on google maps") (mandatory)
8- Handle Exception errors to prevent return error code 500 with API response. (mandatory)
9- Make an admin panel/Control panel with:
 	* CRUD for users with 2 types ('user', 'delivery')
	* Send Push Notification With Firebase to all users with static message.
</pre>
### NOTE:
<pre>
-Make sure You'll handle user verification code  sent by sms
-Make sure You'll handle a not completed profile trying to login even he didn't add the sms code
-Make all needed validation in Admin panel
-Make migrations files with relations
-Make Models files with relations
-Don't forget to share the postman collection for the task
-Save the dummy data by using the seeder
-Make sure that the task is running correctly before send it
</pre>