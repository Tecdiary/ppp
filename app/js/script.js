var socket = null;
try {
    socket = new WebSocket('ws://127.0.0.1:6441');
    socket.onopen = function () {
        var curr = document.getElementsByClassName('status');
        for (var i = 0; i < curr.length; i++) {
            curr[i].classList.add('is-success');
            curr[i].innerHTML = "UP & RUNNING";
        }
        // document.getElementById('run_server').style.display = 'none';
        return;
    };
    socket.onmessage = function (msg) {
        var msg_ele = document.getElementById('message');
        msg_ele.style.display = 'block';
        document.getElementById('notification').className += ' is-info';
        document.getElementById('notification').innerHTML = msg.data;
        setTimeout(function () {
            msg_ele.style.display = 'none';
            document.getElementById('notification').innerHTML = '';
        }, 5000)
        return;
    };
    socket.onclose = function () {
        var curr = document.getElementsByClassName('status');
        for (var i = 0; i < curr.length; i++) {
            curr[i].classList.add('is-danger');
            curr[i].innerHTML = "NOT CONNECTED";
        }
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
        var curr = document.getElementsByClassName('status');
        for (var i = 0; i < curr.length; i++) {
            curr[i].className += ' is-loading';
        }
        setTimeout(function() {
            location.reload();
        }, 500);
        return false;
    }
}

document.addEventListener('DOMContentLoaded', function() {

    if (testp = document.querySelectorAll('.test-print')) {
        for (var i = 0; i < testp.length; i++) {
            testp[i].addEventListener('click', function (e) {
                e.preventDefault();
                var print_id = this.getAttribute('data-printer-id');
                printTest(print_id);
            });
        }
    }

    if (type_ele = document.getElementById("type")) {
        if (type_ele) {
            var type = type_ele.options[type_ele.selectedIndex].value;
            if (type == 'network') {
                document.querySelector('.network').style.display = 'block';
                document.querySelector('.path').style.display = 'none';
            } else {
                document.querySelector('.network').style.display = 'none';
                document.querySelector('.path').style.display = 'block';
            }
        }

        type_ele.onchange = function() {
            var type = this.options[this.selectedIndex].value;
            if (type == 'network') {
                document.querySelector('.network').style.display = 'block';
                document.querySelector('.path').style.display = 'none';
            } else {
                document.querySelector('.network').style.display = 'none';
                document.querySelector('.path').style.display = 'block';
            }
        }
    }

});


function testData() {

    receipt = {};
    receipt.store_name = "Test Biller\n";

    receipt.header = "";
    receipt.header += "Test Biller\n";
    receipt.header += "Biller adddress\n";
    receipt.header += "City Country\n";
    receipt.header += "Tel: 012345678";
    receipt.header += "\n\n";
    receipt.header += "GST Reg: 123456789\n";
    receipt.header += "VAT Reg: 987654321\n";
    receipt.header += "\n";

    receipt.info = "";
    receipt.info += "Date: 08/05/2017 10:38" + "\n";
    receipt.info += "Sale No/Ref: 15" + "\n";
    receipt.info += "Sales Associate: Owner Owner" + "\n\n";
    receipt.info += "Customer: Walk-in Customer" + "\n";

    receipt.items = "";
    receipt.items += "#1 FFR07 - Yellow Watermelon           *Z" + "\n";
    receipt.items += "   2.500 kg x 2.50                   6.25" + "\n";
    receipt.items += "#2 FFR06 - Watermelon                  *Z" + "\n";
    receipt.items += "   2.500 kg x 2.50                   6.25" + "\n";

    receipt.totals = "";
    receipt.totals += "Total:                              12.50" + "\n";
    receipt.totals += "Grand Total:                        12.50" + "\n";
    receipt.totals += "Paid Amount:                        12.50" + "\n";
    receipt.totals += "Due Amount:                          0.00" + "\n";

    receipt.payments = '';
    receipt.payments += "Paid by:                             Cash" + "\n";
    receipt.payments += "Amount:                             12.50" + "\n";
    receipt.payments += "Change:                              0.00" + "\n";

    receipt.footer = "";
    receipt.footer += " Thank you for shopping with us. \nPlease come again\n\n";

    return receipt;

}

function printTest(printer_id) {
    if (socket.readyState == 1) {
        for (var i = 0; i < printers.length; i++) {
            if (printers[i].id == printer_id) {
                printer = printers[i];
            }
        }
        var receipt_data = testData();
        var socket_data = {
            'printer': printer,
            logo: 'logo1.png',
            'text': receipt_data
        };
        socket.send(JSON.stringify({
            type: 'print-receipt',
            data: socket_data
        }));
        return false;
    } else {
        alert('Unable to connect to socket, please make sure that server is up and running fine.');
        return false;
    }
}
