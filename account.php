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

$response = $client->requestTokenAccount($account_key, $ansible_session);

$token = $response->token;
$_SESSION['ansible']['session'] = $response->session;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
        .ansible-chat-messages > div {
            display: none;
        }

        .ansible-chat-messages > div.active {
            display: block;
        }

        .ansible-thread-form {
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Account</h1>

    <div class="row">
        <div class="col-md-3">
            <div class="ansible-thread-form">
                <form role="form" onsubmit="return false;">
                    <div class="input-group">
                        <input name="subject" type="text" class="form-control"/>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">Add</button>
                        </span>
                    </div>
                </form>
            </div>
            <ul class="thread-tablist nav nav-pills nav-stacked"></ul>
        </div>
        <div class="col-md-9">
            <!-- Tab panes -->
            <div class="ansible-chat-messages"></div>
            <div class="ansible-chat-form">
                <form role="form" onsubmit="return false;">
                    <div class="input-group">
                        <input name="message" type="text" class="form-control"/>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">Send</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script type="application/javascript" src="http://localhost:8081/browserify_standalone_bundle/ansible.js"></script>
<script type="application/javascript">


    function base64url_encode(str) {
        return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/\=+$/, '');
    }

    function base64ulr_decode(str) {
        str = (str + '===').slice(0, str.length + (str.length % 4));
        return atob(str.replace(/-/g, '+').replace(/_/g, '/'));
    }

    $(function () {
        if (!window.ansible) {
            return;
        }

        var ansible = window.ansible.createAnsible(<?php echo json_encode($api_key); ?>);
        var currentTo = null;

        ansible.connectAccount(<?php echo json_encode($token); ?>, function (error, connection) {
            if (error) {
                console.error(error);
                return;
            }

            connection.on('say', function (from, to, message) {
                appendIncomingMessage(from, message);
            });



            $('.ansible-thread-form form').on('submit', function () {
                var values = $(this).serializeArray();
                var subject = '';


                $.each(values, function (index) {
                    if (this.name == 'subject') {
                        subject = this.value;
                    }
                });

                subject = $.trim(subject);

                if (subject.length > 0) {
                    this.reset();
                    subject = '(account)'+subject;
                    appendTreadTab(base64url_encode(subject), subject, true);
                }
            });

            $('.ansible-chat-form form').on('submit', function () {
                if (!currentTo) {
                    return;
                }

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
                    connection.say(currentTo, message);
                    appendOutgoingMessage(currentTo, message);
                }
            });
        });


        function appendTreadTab(id, label, isActive) {

            var html = '<li ';

            if (isActive === true) {
                html += 'class="active"'
            }

            html += ' ><a href="#' + id + '">' + label + ' <button class="close" title="Close this thead">×</button></a></li>';

            var item = $(html).appendTo(".thread-tablist");


            item.click(function (event) {

                $('.thread-tablist li.active, .ansible-chat-messages > div.active').removeClass('active');
                var $this = $(this);
                $this.addClass('active');
                $($this.find('[href]').attr('href')).addClass('active');

                currentTo = label;

            });

            item.find('.close').click(function (event) {
                event.preventDefault();
                event.stopPropagation();

                var $tab = $(this).parents('li');

                $($tab.find('[href]').attr('href')).remove();

                $tab.remove();
            });

            html = '<div id="' + id + '"';
            if (isActive) {
                html += ' class="active" ';
            }
            html += '></div>';

            $('.ansible-chat-messages').append(html);

            if (isActive) {
                currentTo = label;
            }

        }

        function appendIncomingMessage(from, message) {
            if (message.length <= 0) {
                return;
            }

            var idMessage = base64url_encode(from);
            var $messages = $('#' + idMessage);
            var paragraph = '<p>' + message + '</p>';

            if ($messages.size() <= 0) {
                appendTreadTab(idMessage, from, $('.ansible-chat-messages > div').size() === 0);
                $messages = $('#' + idMessage);
            }

            var last = $messages.find('blockquote').last();

            if ((last.size() > 0) && !last.hasClass('blockquote-reverse')) {
                last.find('footer').before(paragraph);
            } else {
                $messages.append('<blockquote>' + paragraph + '<footer>' + from + '</footer></blockquote>');
            }
        }

        function appendOutgoingMessage(to, message) {
            if (message.length <= 0) {
                return;
            }
            var a = 'You';
            var idMessage = base64url_encode(to);
            var $messages = $('#' + idMessage);

            var paragraph = '<p>' + message + '</p>';
            var last = $messages.find('blockquote').last();

            if (last.hasClass('blockquote-reverse')) {
                last.find('footer').before(paragraph);
            } else {
                $messages.append('<blockquote class="blockquote-reverse">' + paragraph + '<footer>' + a + '</footer></blockquote>');
            }
        }


    });

</script>

</body>
</html>