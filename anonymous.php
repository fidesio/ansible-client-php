<?php

session_start();

/** @var \Composer\Autoload\ClassLoader $autoload */
$autoload = require './vendor/autoload.php';

$autoload->add('Ansible', __DIR__ . '/source');


use Ansible\Client as AnsibleClient;

$api_key = 'oqwdnqowidnqoinwd';
$api_secret = 'jwlkejbfkwjbef';
$account_key = 'asdasd/asddasd';

if (isset($_GET['account'])) {
    $account_key = $_GET['account'];
}

$client = new AnsibleClient($api_key, $api_secret);

$ansible_session = (isset($_SESSION['ansible']['session']) ? $_SESSION['ansible']['session'] : null);

$response = $client->requestTokenAnonymousToAccount($account_key, $ansible_session);

$token = $response->token;
$_SESSION['ansible']['session'] = $response->session;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AnonymousToAccount</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
        .ansible-chat-form {
            position: absolute;
            width: 100%;
            bottom: 0;
            left: 0;

            margin: 0 0 1em;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>AnonymousToAccount</h1>
</div>
<div class="ansible-chat-form">
    <div class="container">

        <div class="ansible-chat-messages">
        </div>

        <form role="form" onsubmit="return false;">
            <div class="input-group">
                <input name="message" type="text" class="form-control">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit">Send !</button>
                </span>
            </div>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script type="application/javascript" src="http://localhost:8081/browserify_standalone_bundle/ansible.js"></script>
<script type="application/javascript">
    $(function () {
        if (!window.ansible) {
            return;
        }


        function appendOutgoingMessage(message) {
            if (message.length <= 0) {
                return;
            }
            var from = 'You';
            var $messages = $('.ansible-chat-messages');

            var paragraph = '<p>' + message + '</p>';
            var last = $messages.find('blockquote').last();

            if (last.hasClass('blockquote-reverse')) {
                last.find('footer').before(paragraph);
            } else {
                $messages.append('<blockquote class="blockquote-reverse">' + paragraph + '<footer>' + from + '</footer></blockquote>');
            }
        }


        function appendIncomingMessage(message) {
            if (message.length <= 0) {
                return;
            }

            var from = 'Other guy';
            var $messages = $('.ansible-chat-messages');
            var last = $messages.find('blockquote').last();
            var paragraph = '<p>' + message + '</p>';

            if ((last.size() > 0) && !last.hasClass('blockquote-reverse')) {
                last.find('footer').before(paragraph);
            } else {
                $messages.append('<blockquote>' + paragraph + '<footer>' + from + '</footer></blockquote>');
            }
        }

        var ansible = window.ansible.createAnsible(<?php echo json_encode($api_key); ?>);

        ansible.connectAnonymousToAccount(<?php echo json_encode($token); ?>, function (error, connection) {
            if (error) {
                console.error(error);
                return;
            }

            connection.on('say', function (from, to, message) {
                appendIncomingMessage(message);
            });


            $('.ansible-chat-form form').on('submit', function () {
                var values = $(this).serializeArray();
                var message = '';

                $.each(values, function (index) {
                    if (this.name == 'message') {
                        message = this.value;
                    }
                });

                message = $.trim(message);

                if (message.length > 0) {
                    this.reset();
                    connection.say(message);

                    appendOutgoingMessage(message);
                }
            });
        });


    });
</script>

</body>
</html>