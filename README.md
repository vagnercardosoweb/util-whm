## WHM [ONESIGNAL](https://documentation.cpanel.net/display/SDK/Guide+to+WHM+API+1)

Para utilização da classe é só seguir os passos abaixo.

````php
<?php

// Configurações
define('API_HOST', ''); // Host que acessa o WHM
define('API_USER', ''); // Usuário que acessa o WHM
define('API_HASH', ''); // Remote Acess Key do WHM
define('SUSPEND_EMAIL', ''); // Conta que vai suspender o e-mail
  
// Instancia a classe
$whm = new WHM(API_HOST, API_USER, API_HASH);

// Suspender e-mail
//echo $whm->suspend_outgoing_email(SUSPEND_EMAIL);

// Voltar e-mail
//echo $whm->unsuspend_outgoing_email(SUSPEND_EMAIL);
````
