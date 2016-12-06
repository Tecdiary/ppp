<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PHP POS Print Server</title>

    <script>
    var socket = null;
    try {
        socket = new WebSocket('ws://127.0.0.1:6441');
        socket.onopen = function () {
            document.getElementById('curr').innerHTML = "<span style='color:green;'>UP & RUNNING</span>";
            return;
        };
        socket.onmessage = function (msg) {
            document.getElementById('message').innerHTML = msg.data;
            setTimeout(function () {
                document.getElementById('message').innerHTML = '';
            }, 5000)
            return;
        };
        socket.onclose = function () {
            document.getElementById('curr').innerHTML = "<span style='color:red;'>NOT CONNECTED</span>";
            return;
        };
    } catch (e) {
        console.log(e);
    }
    checkStatus = function() {
        if (socket.readyState == 1) {
            socket.send(JSON.stringify({
                type: 'check-status'
            }));
            return false;
        } else {
            document.getElementById('message').innerHTML = 'Unable to connect to server.';
            return false;
        }
    }
    </script>

    <!-- Styles -->
    <style type="text/css" media="screen">
    @font-face { font-family: 'Raleway'; font-style: normal; font-weight: 100; src: local('Raleway Thin'), local('Raleway-Thin'), url(fonts/RJMlAoFXXQEzZoMSUteGWFtXRa8TVwTICgirnJhmVJw.woff2) format('woff2'); unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2212, U+2215, U+E0FF, U+EFFD, U+F000; }
    @font-face { font-family: 'Raleway'; font-style: normal; font-weight: 600; src: local('Raleway SemiBold'), local('Raleway-SemiBold'), url(fonts/xkvoNo9fC8O2RDydKj12b_k_vArhqVIZ0nv9q090hN8.woff2) format('woff2'); unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2212, U+2215, U+E0FF, U+EFFD, U+F000; }
    html, body { background-color: #fff; color: #636b6f; font-family: 'Raleway', sans-serif; font-weight: 100; height: 100vh; margin: 0; }
    .full-height { height: 100vh; }
    .flex-center { align-items: center; display: flex; justify-content: center; }
    .position-ref { position: relative; }
    .top-right { position: absolute; right: 10px; top: 18px; }
    .content { text-align: center; }
    .title { font-size: 48px; line-height: 48px; margin-bottom: 15px; }
    #message, .info { margin: 20px 0; line-height: 18px; }
    #message > span { text-transform: none; color: blue; }
    #message, .info, .links > a { color: #636b6f; padding: 0 25px; font-size: 12px; font-weight: 600; letter-spacing: .1rem; text-decoration: none; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="title">
                PHP POS Print Server
            </div>
            <div class="links">
                <a href="#" id="curr" onclick="return checkStatus()">Checking...</a>
            </div>
            <div id="message"></div>
            <div class="info">&copy; 2017 @ tecdiary.com</div>
        </div>
    </div>
</body>
</html>
