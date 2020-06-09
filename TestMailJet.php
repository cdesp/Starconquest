<?php
echo "Ok"; 
  require 'lib/vendor/autoload.php';
  
  use \lib\Mailjet\Resources;
  echo "OK1";
  $mj = new \lib\Mailjet\Client('2a2e513a2087a7b45e8afeafa33b6f81','6ee8a087fa1a051398b667f2381b8e2c',false,['version' => 'v3.1']);
 echo "Ok1a";
  $body = [
    'Messages' => [
      [
        'From' => [
          'Email' => "cdesp72@gmail.com",
          'Name' => "Chris"
        ],
        'To' => [
          [
            'Email' => "cdesp72@gmail.com",
            'Name' => "Chris"
          ]
        ],
        'Subject' => "Greetings from Mailjet.",
        'TextPart' => "My first Mailjet email",
        'HTMLPart' => "<h3>Dear passenger 1, welcome to <a href='https://www.mailjet.com/'>Mailjet</a>!</h3><br />May the delivery force be with you!",
        'CustomID' => "AppGettingStartedTest"
      ]
    ]
  ];
  echo "OK2";
  $response = $mj->post(Resources::$Email, ['body' => $body]);
  echo "OK3";
  $response->success() && var_dump($response->getData());
?>